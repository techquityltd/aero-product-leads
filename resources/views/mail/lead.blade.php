@extends('mail.layout')

@section('content')
    <h1>New Lead</h1>

    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="input">
        <tbody>
        <tr>
            <td align="left">
                <label>Customer Name: </label>
            </td>
            <td align="left">
                {{ $customerName }}
            </td>
        </tr>
        <tr>
            <td align="left">
                <label>Customer Email: </label>
            </td>
            <td align="left">
                {{ $order->email }}
            </td>
        </tr>
        <tr>
            <td align="left">
                <label>Customer Phone: </label>
            </td>
            <td align="left">
                {{ $customerPhone }}
            </td>
        </tr>
        <tr>
            <td align="left">
                <label>Customer Address: </label>
            </td>
            <td align="left">
                {{ $customerAddress }}
            </td>
        </tr>
        <tr>
            <td align="left">
                <label>Customer Postcode: </label>
            </td>
            <td align="left">
                {{ $order->shippingAddress->postcode ?? 'N/A' }}
            </td>
        </tr>
        <tr>
            <td align="left">
                <label>Order Reference: </label>
            </td>
            <td align="left">
                {{ $order->reference }}
            </td>
        </tr>
        </tbody>
    </table>

    <h2>Lead Products</h2>

    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="input">
        <thead>
            <tr>
                <th align="left">Product Name</th>
                <th align="left">SKU</th>
                <th align="left">Quantity</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($orderItems as $item)
                <tr>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->sku }}</td>
                    <td>{{ $item->quantity }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection