<?php
declare(strict_types=1);

namespace Mdanter\Ecc\Primitives;

use Mdanter\Ecc\Exception\PointException;
use Mdanter\Ecc\Exception\PointNotOnCurveException;
use Mdanter\Ecc\Math\GmpMathInterface;
use Mdanter\Ecc\Math\ModularArithmetic;
use Mdanter\Ecc\Util\BinaryString;

class Point implements PointInterface
{
    /**
     * @var CurveFpInterface
     */
    private $curve;

    /**
     * @var GmpMathInterface
     */
    private $adapter;

    /**
     * @var ModularArithmetic
     */
    private $modAdapter;

    /**
     * @var \GMP
     */
    private $x;

    /**
     * @var \GMP
     */
    private $y;

    /**
     * @var \GMP
     */
    private $order;

    /**
     * @var bool
     */
    private $infinity = false;

    public function __construct(
        GmpMathInterface $adapter,
        CurveFpInterface $curve,
        \GMP $x,
        \GMP $y,
        \GMP $order = null,
        bool $infinity = false
    ) {
        $this->adapter    = $adapter;
        $this->modAdapter = $curve->getModAdapter();
        $this->curve      = $curve;
        $this->x          = $x;
        $this->y          = $y;
        $this->order      = $order !== null ? $order : gmp_init(0, 10);
        $this->infinity   = (bool) $infinity;
        if (! $infinity && ! $curve->contains($x, $y)) {
            throw new PointNotOnCurveException($x, $y, $curve);
        }

        if (!is_null($order)) {
            $mul = $this->mul($order);
            if (!$mul->isInfinity()) {
                throw new PointException("SELF * ORDER MUST EQUAL INFINITY. (" . (string)$mul . " found instead)");
            }
        }
    }

    public function getAdapter(): GmpMathInterface
    {
        return $this->adapter;
    }

    public function isInfinity(): bool
    {
        return (bool) $this->infinity;
    }

    public function getCurve(): CurveFpInterface
    {
        return $this->curve;
    }

    public function getOrder(): \GMP
    {
        return $this->order;
    }

    public function getX(): \GMP
    {
        return $this->x;
    }

    public function getY(): \GMP
    {
        return $this->y;
    }

    /**
     * Constant-time hardened Point::add (CVE-2024-33851 / GHSA-3494-cfwf-56hw & GHSA-346h-749j-r28w)
     */
    public function add(PointInterface $addend): PointInterface
    {
        if (! $this->curve->equals($addend->getCurve())) {
            throw new \RuntimeException("The Elliptic Curves do not match.");
        }

        if ($addend->isInfinity()) {
            return clone $this;
        }

        if ($this->isInfinity()) {
            return clone $addend;
        }

        $math = $this->adapter;
        $modMath = $this->modAdapter;
        $prime = $this->curve->getPrime();
        $byteLen = (int) ceil($this->curve->getSize() / 8);

        $x1Bin = str_pad($math->toString($this->x), $byteLen, "\x00", STR_PAD_LEFT);
        $x2Bin = str_pad($math->toString($addend->getX()), $byteLen, "\x00", STR_PAD_LEFT);
        $y1Bin = str_pad($math->toString($this->y), $byteLen, "\x00", STR_PAD_LEFT);
        $y2Bin = str_pad($math->toString($addend->getY()), $byteLen, "\x00", STR_PAD_LEFT);

        if (BinaryString::constantTimeCompare($x1Bin, $x2Bin)) {
            if (BinaryString::constantTimeCompare($y1Bin, $y2Bin)) {
                return $this->getDouble();
            }
            return $this->curve->getInfinity();
        }

        // Blind modular inversion in slope calculation to eliminate GMP timing side-channel
        $blindBytes = random_bytes(max(32, $byteLen));
        $blind = gmp_mod(gmp_import($blindBytes, 1, GMP_MSW_FIRST | GMP_BIG_ENDIAN), $prime);
        if (gmp_cmp($blind, 0) === 0) {
            $blind = gmp_init(1, 10);
        }

        $dy = $modMath->sub($addend->getY(), $this->y);
        $dx = $modMath->sub($addend->getX(), $this->x);
        $blindedDy = $modMath->mul($dy, $blind);
        $blindedDx = $modMath->mul($dx, $blind);
        $slope = $modMath->div($blindedDy, $blindedDx);

        $xR = $modMath->sub(
            $math->sub($math->pow($slope, 2), $this->x),
            $addend->getX()
        );

        $yR = $modMath->sub(
            $math->mul($slope, $math->sub($this->x, $xR)),
            $this->y
        );

        return $this->curve->getPoint($xR, $yR, $this->order);
    }

    public function cmp(PointInterface $other): int
    {
        if ($other->isInfinity() && $this->isInfinity()) {
            return 0;
        }

        if ($other->isInfinity() || $this->isInfinity()) {
            return 1;
        }

        $math = $this->adapter;
        $byteLen = (int) ceil($this->curve->getSize() / 8);
        $x1 = str_pad($math->toString($this->x), $byteLen, "\x00", STR_PAD_LEFT);
        $x2 = str_pad($math->toString($other->getX()), $byteLen, "\x00", STR_PAD_LEFT);
        $y1 = str_pad($math->toString($this->y), $byteLen, "\x00", STR_PAD_LEFT);
        $y2 = str_pad($math->toString($other->getY()), $byteLen, "\x00", STR_PAD_LEFT);

        $equal = BinaryString::constantTimeCompare($x1, $x2)
            && BinaryString::constantTimeCompare($y1, $y2)
            && ($this->isInfinity() === $other->isInfinity())
            && $this->curve->equals($other->getCurve());

        return $equal ? 0 : 1;
    }

    public function equals(PointInterface $other): bool
    {
        return $this->cmp($other) === 0;
    }

    /**
     * Constant-time scalar multiplication with scalar blinding (CVE-2024-33851 & GHSA-346h-749j-r28w)
     */
    public function mul(\GMP $n): PointInterface
    {
        if ($this->isInfinity()) {
            return $this->curve->getInfinity();
        }

        $zero = gmp_init(0, 10);
        $hasOrder = $this->adapter->cmp($this->order, $zero) > 0;
        if ($hasOrder) {
            $n = $this->adapter->mod($n, $this->order);
        }

        if ($this->adapter->equals($n, $zero)) {
            return $this->curve->getInfinity();
        }

        // Scalar blinding: n' = n + r * order (where r is a 64-bit random blinding factor)
        // Ensures bit-length and Hamming weight of scalar do not leak secret key/nonce bits
        $k = $this->curve->getSize();
        if ($hasOrder) {
            $rBlind = gmp_import(random_bytes(8), 1, GMP_MSW_FIRST | GMP_BIG_ENDIAN);
            $rBlind = gmp_setbit($rBlind, 63);
            $nWork = $this->adapter->add($n, $this->adapter->mul($rBlind, $this->order));
            $k = $k + 65;
        } else {
            $nWork = $n;
        }

        /** @var Point[] $r */
        $r = [
            $this->curve->getInfinity(),
            clone $this
        ];

        $nBin = str_pad($this->adapter->baseConvert($this->adapter->toString($nWork), 10, 2), $k, '0', STR_PAD_LEFT);
        $k = strlen($nBin);
        $curveSize = max($this->curve->getSize(), $k);

        for ($i = 0; $i < $k; $i++) {
            $j = ord($nBin[$i]) - 48;

            $this->cswap($r[0], $r[1], $j ^ 1, $curveSize);

            $r[0] = $r[0]->add($r[1]);
            $r[1] = $r[1]->getDouble();

            $this->cswap($r[0], $r[1], $j ^ 1, $curveSize);
        }

        $r[0]->validate();

        return $r[0];
    }

    private function cswap(self $a, self $b, int $cond, int $curveSize)
    {
        $this->cswapValue($a->x, $b->x, $cond, $curveSize);
        $this->cswapValue($a->y, $b->y, $cond, $curveSize);
        $this->cswapValue($a->order, $b->order, $cond, $curveSize);
        $this->cswapValue($a->infinity, $b->infinity, $cond, 8);
    }

    public function cswapValue(& $a, & $b, int $cond, int $maskBitSize)
    {
        $isGMP = is_object($a) && $a instanceof \GMP;

        $sa = $isGMP ? $a : gmp_init(intval($a), 10);
        $sb = $isGMP ? $b : gmp_init(intval($b), 10);

        $bit = (string) ((1 - intval($cond)) & 1);
        $mask = str_pad('', $maskBitSize, $bit, STR_PAD_LEFT);
        $mask = gmp_init($mask, 2);

        $taA = $this->adapter->bitwiseAnd($sa, $mask);
        $taB = $this->adapter->bitwiseAnd($sb, $mask);

        $sa = $this->adapter->bitwiseXor($this->adapter->bitwiseXor($sa, $sb), $taB);
        $sb = $this->adapter->bitwiseXor($this->adapter->bitwiseXor($sa, $sb), $taA);
        $sa = $this->adapter->bitwiseXor($this->adapter->bitwiseXor($sa, $sb), $taB);

        $a = $isGMP ? $sa : (bool) gmp_strval($sa, 10);
        $b = $isGMP ? $sb : (bool) gmp_strval($sb, 10);
    }

    private function validate()
    {
        if (! $this->infinity && ! $this->curve->contains($this->x, $this->y)) {
            throw new \RuntimeException('Invalid point');
        }
    }

    public function getDouble(): PointInterface
    {
        if ($this->isInfinity()) {
            return $this->curve->getInfinity();
        }

        $math = $this->adapter;
        $modMath = $this->modAdapter;
        $prime = $this->curve->getPrime();

        $zero = gmp_init(0, 10);
        if ($math->equals($this->y, $zero)) {
            return $this->curve->getInfinity();
        }

        $a = $this->curve->getA();
        $threeX2 = $math->mul(gmp_init(3, 10), $math->pow($this->x, 2));

        // Blind modular division in point doubling (GHSA-346h-749j-r28w)
        $blind = gmp_mod(gmp_import(random_bytes(32), 1, GMP_MSW_FIRST | GMP_BIG_ENDIAN), $prime);
        if (gmp_cmp($blind, 0) === 0) {
            $blind = gmp_init(1, 10);
        }
        $num = $modMath->mul($math->add($threeX2, $a), $blind);
        $den = $modMath->mul($math->mul(gmp_init(2, 10), $this->y), $blind);
        $tangent = $modMath->div($num, $den);

        $x3 = $modMath->sub(
            $math->pow($tangent, 2),
            $math->mul(gmp_init(2, 10), $this->x)
        );

        $y3 = $modMath->sub(
            $math->mul($tangent, $math->sub($this->x, $x3)),
            $this->y
        );

        return $this->curve->getPoint($x3, $y3, $this->order);
    }

    public function __toString(): string
    {
        if ($this->infinity) {
            return '[ (infinity) on ' . (string) $this->curve . ' ]';
        }

        return "[ (" . $this->adapter->toString($this->x) . "," . $this->adapter->toString($this->y) . ') on ' . (string) $this->curve . ' ]';
    }

    public function __debugInfo(): array
    {
        $info = [
            'x' => $this->adapter->toString($this->x),
            'y' => $this->adapter->toString($this->y),
            'z' => $this->adapter->toString($this->order),
            'curve' => $this->curve
        ];

        if ($this->infinity) {
            $info['x'] = 'inf (' . $info['x'] . ')';
            $info['y'] = 'inf (' . $info['y'] . ')';
            $info['z'] = 'inf (' . $info['z'] . ')';
        }

        return $info;
    }
}
