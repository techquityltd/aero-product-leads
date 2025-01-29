@extends('mail.layout')

@section('content')
    <h1>New Product Lead</h1>

    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="input ">
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
                {{ $lead->order->email }}
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
                {{ $lead->postcode }}
            </td>
        </tr>
        <tr>
            <td align="left">
                <label>Order Reference: </label>
            </td>
            <td align="left">
                {{ $lead->order->reference }}
            </td>
        </tr>
        <tr>
            <td align="left">
                <label>Lead Product: </label>
            </td>
            <td align="left">
                {{ $lead->orderItem->name }} (SKU: {{ $lead->orderItem->sku }})
            </td>
        </tr>
        </tbody>
    </table>
@endsection
