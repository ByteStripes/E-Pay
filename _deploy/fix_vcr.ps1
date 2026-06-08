$src = 'C:\Program Files\Microsoft Visual Studio\18\Community\VC\Redist\MSVC\14.50.35710\x64\Microsoft.VC145.CRT'
$dst = 'D:\phpstudy_pro\Extensions\php\php8.2.9nts'
$files = @('vcruntime140.dll','vcruntime140_1.dll','msvcp140.dll','concrt140.dll')
foreach ($f in $files) {
  $s = Join-Path $src $f
  if (Test-Path $s) {
    Copy-Item -Path $s -Destination (Join-Path $dst $f) -Force
    '{0} copied (ver {1})' -f $f, (Get-Item $s).VersionInfo.FileVersion
  } else {
    '{0} not found in redist' -f $f
  }
}
