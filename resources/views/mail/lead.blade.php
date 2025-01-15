@extends('mail.layout')

@section('content')
    <h1>{{ $emailType === 'first' ? 'New Product Lead' : 'Follow-up Product Lead' }}</h1>

    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="input ">
        <tbody>
        <tr>
            <td align="left">
                <label>Customer Name:</label>
            </td>
            <td align="left">
                {{ $customerName }}
            </td>
        </tr>
        <tr>
            <td align="left">
                <label>Customer Address</label>
            </td>
            <td align="left">
                {{ $customerAddress }}
            </td>
        </tr>
        <tr>
            <td align="left">
                <label>Customer Postcode:</label>
            </td>
            <td align="left">
                {{ $lead->postcode }}
            </td>
        </tr>
        <tr>
            <td align="left">
                <label>Order ID:</label>
            </td>
            <td align="left">
                {{ $lead->order_id }}
            </td>
        </tr>
        <tr>
            <p><strong>Product(s):</strong></p>
            <ul>
                @foreach($lead->order->orderItems as $orderItem)
                    <li>{{ $orderItem->name }}</li>
                @endforeach
            </ul>
        </tr>
        </tbody>
    </table>
@endsection
