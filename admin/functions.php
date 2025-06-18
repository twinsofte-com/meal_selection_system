<?php
function generateQRCode($data, $filename) {
    require_once '../phpqrcode/qrlib.php';
    QRcode::png($data, $filename, QR_ECLEVEL_L, 4); // QR_ECLEVEL_L is error correction level, 4 is the size of the QR code
}
?>
