@extends('mail.layout')

@section('content')
    <h1>New Form Lead</h1>

    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="input">
        <tbody>
        <tr>
            <td align="left">
                <label>Customer Name: </label>
            </td>
            <td align="left">
                {{ $lead->customer_name ?? 'N/A' }}
            </td>
        </tr>
        <tr>
            <td align="left">
                <label>Customer Email: </label>
            </td>
            <td align="left">
                {{ $lead->customer_email ?? 'N/A' }}
            </td>
        </tr>
        <tr>
            <td align="left">
                <label>Customer Postcode: </label>
            </td>
            <td align="left">
                {{ $lead->postcode ?? 'N/A' }}
            </td>
        </tr>
        <tr>
            <td align="left">
                <label>Customer Phone: </label>
            </td>
            <td align="left">
                {{ $lead->customer_contact_number ?? 'N/A' }}
            </td>
        </tr>
        </tbody>
    </table>

    @if ($product)
        <h2>Lead Product</h2>

        <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="input">
            <thead>
            <tr>
                <th align="left">Product Name</th>
                <th align="left">SKU</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>{{ $product->name }}</td>
                <td>{{ $variant->sku ?? 'N/A' }}</td>
            </tr>
            </tbody>
        </table>
    @else
        <p><em>Product information not available.</em></p>
    @endif
@endsection
