<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fitur Pembayaran</title>
    <link rel="stylesheet" href="Kartuatm.css">
</head>
<body>
    <div class="payment-container">
        <div class="payment-header">
            <h2>Pilih Metode Pembayaran</h2>
            <button class="close-btn" onclick="closePayment()">✖</button>
        </div>
        <div class="payment-section">
            <h3>Credit Card</h3>
            <ul class="payment-list">
                <li>
                    <img src="mscard.jpg" alt="MasterCard"> Master Card
                </li>
                <li>
                    <img src="visa.jpg" alt="Visa"> VISA
                </li>
            </ul>
        </div>
        <div class="payment-section">
            <h3>Debit Card</h3>
            <ul class="payment-list">
                <li>
                    <img src="mandiri.jpg" alt="Bank Mandiri"> Bank Mandiri
                </li>
                <li>
                    <img src="bri.jpg" alt="Bank BRI"> Bank BRI
                </li>
                <li>
                    <img src="jenius.jpg" alt="Jenius"> Jenius
                </li>
            </ul>
        </div>
        <div class="payment-section">
            <h3>eWallet</h3>
            <ul class="payment-list">
                <li>
                    <img src="dana.jpg" alt="DANA"> DANA
                </li>
                <li>
                    <img src="gopey.jpg" alt="GOPAY"> GOPAY
                </li>
                <li>
                    <img src="ovo.jpg" alt="OVO"> OVO
                </li>
                <li>
                    <img src="spay.jpg" alt="ShopeePay"> ShopeePay
                </li>
            </ul>
        </div>
    </div>
    <script>
        function closePayment() {
            document.querySelector('.payment-container').style.display = 'none';
        }
    </script>
</body>
</html>
