<!DOCTYPE html>
<html>
<head>
    <title>Customer QR Code</title>
    <style>
        body { text-align: center; font-family: sans-serif; margin-top: 50px; }
        img { margin-top: 20px; }
    </style>
</head>
<body>
    <h2>{{ $customer->first_name }} {{ $customer->last_name }}</h2>
    <p>Customer No: {{ $customer->customer_no }}</p>
    <img src="{{ $qrImage }}" alt="QR Code">
</body>
</html>
