<?php

declare(strict_types=1);

namespace Mdanter\Ecc\Crypto\Signature;

use Mdanter\Ecc\Math\GmpMathInterface;
use Mdanter\Ecc\Crypto\Key\PrivateKeyInterface;
use Mdanter\Ecc\Crypto\Key\PublicKeyInterface;
use Mdanter\Ecc\Util\BinaryString;

class Signer
{
    /**
     * @var GmpMathInterface
     */
    private $adapter;

    public function __construct(GmpMathInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Constant-time blinded ECDSA signing with canonical low-S normalization (GHSA-346h-749j-r28w)
     *
     * @param PrivateKeyInterface $key
     * @param \GMP $truncatedHash
     * @param \GMP $randomK
     * @return SignatureInterface
     */
    public function sign(PrivateKeyInterface $key, \GMP $truncatedHash, \GMP $randomK): SignatureInterface
    {
        $math = $this->adapter;
        $generator = $key->getPoint();
        $order = $generator->getOrder();
        $modMath = $math->getModularArithmetic($order);

        $k = $math->mod($randomK, $order);
        $p1 = $generator->mul($k);
        $r = $math->mod($p1->getX(), $order);
        $zero = gmp_init(0, 10);
        if ($math->equals($r, $zero)) {
            throw new \RuntimeException("Error: random number R = 0");
        }

        // Multiplicative blinding of k and secret key d so GMP inverseMod never runs on unblinded k
        $blind = $math->mod(gmp_import(random_bytes(32), 1, GMP_MSW_FIRST | GMP_BIG_ENDIAN), $order);
        if ($math->equals($blind, $zero)) {
            $blind = gmp_init(1, 10);
        }
        $kBlinded = $modMath->mul($k, $blind);
        $numBlinded = $modMath->mul(
            $modMath->add($truncatedHash, $modMath->mul($key->getSecret(), $r)),
            $blind
        );
        $s = $modMath->div($numBlinded, $kBlinded);
        if ($math->equals($s, $zero)) {
            throw new \RuntimeException("Error: random number S = 0");
        }

        // Canonical low-S normalization to prevent ECDSA signature malleability
        $halfOrder = $math->rightShift($order, 1);
        if ($math->cmp($s, $halfOrder) > 0) {
            $s = $math->sub($order, $s);
        }

        return new Signature($r, $s);
    }

    /**
     * @param PublicKeyInterface $key
     * @param SignatureInterface $signature
     * @param \GMP $hash
     * @return bool
     */
    public function verify(PublicKeyInterface $key, SignatureInterface $signature, \GMP $hash): bool
    {
        $generator = $key->getGenerator();
        $n = $generator->getOrder();
        $r = $signature->getR();
        $s = $signature->getS();

        $math = $this->adapter;
        $one = gmp_init(1, 10);
        if ($math->cmp($r, $one) < 0 || $math->cmp($r, $math->sub($n, $one)) > 0) {
            return false;
        }

        if ($math->cmp($s, $one) < 0 || $math->cmp($s, $math->sub($n, $one)) > 0) {
            return false;
        }

        $modMath = $math->getModularArithmetic($n);
        $c = $math->inverseMod($s, $n);
        $u1 = $modMath->mul($hash, $c);
        $u2 = $modMath->mul($r, $c);
        $xy = $generator->mul($u1)->add($key->getPoint()->mul($u2));
        if ($xy->isInfinity()) {
            return false;
        }
        $v = $math->mod($xy->getX(), $n);

        $byteLen = (int) ceil($generator->getCurve()->getSize() / 8);
        $vBin = str_pad($math->toString($v), $byteLen, "\x00", STR_PAD_LEFT);
        $rBin = str_pad($math->toString($r), $byteLen, "\x00", STR_PAD_LEFT);

        return BinaryString::constantTimeCompare($vBin, $rBin);
    }
}
