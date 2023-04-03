<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Test Email</title>
</head>
<body>
    <h1>New Order</h1>
    <p>Transaction ID: <strong>{{ $order['transaction_id'] }}</strong></p>
    <p>Name: {{ $order['name'] }}</p>
    <p>E-mail: {{ $order['email'] }}</p>
    <p>Telephone: {{ $order['telephone'] }}</p>
    <p>Shipping Address: {{ $order['shipping_address'] }}</p>
    <p>Invoice Address: {{ $order['invoice_address'] }}</p>
</body>
</html>
