@extends('mail.layout')

@section('content')
    <p>You have a new Lead</p>

    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="input ">
        <tbody>
        <tr>
            <td align="left">
                <label>Customer Name:</label>
            </td>
            <td align="left">
                {{ $data['name'] }}
            </td>
        </tr>
        <tr>
            <td align="left">
                <label>E-mail</label>
            </td>
            <td align="left">
                {{ $data['email'] }}
            </td>
        </tr>
        <tr>
            <td align="left">
                <label>Telephone:</label>
            </td>
            <td align="left">
                {{ $data['telephone'] }}
            </td>
        </tr>
        </tbody>
    </table>
@endsection
