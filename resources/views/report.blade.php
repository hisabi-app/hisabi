<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Monthly Report</title>

    <style>
        body {
            background: #fff none;
            font-family: DejaVu Sans, 'sans-serif';
            font-size: 12px;
        }

        h2 {
            font-size: 28px;
            color: #ccc;
        }

        .container {
            padding-top: 30px;
        }

        .invoice-head td {
            padding: 0 8px;
        }

        .table th {
            vertical-align: bottom;
            font-weight: bold;
            padding: 8px;
            line-height: 14px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .table tr.row td {
            border-bottom: 1px solid #ddd;
        }

        .table td {
            padding: 8px;
            line-height: 14px;
            text-align: left;
            vertical-align: top;
        }
    </style>
</head>
<body>
    <div class="container">
        <table style="margin-left: auto; margin-right: auto;" width="550">
            <tr>
                <td width="160">
                    <span style="font-size: 28px;">
                        FINANCE
                    </span>
                </td>

                <td align="right">
                    Monthly Report <br>
                    <strong>{{ $month }}</strong>
                </td>
            </tr>
            <tr style="height: 3px; background: lightgray; width: 100%;">
                <td colspan="2"></td>
            </tr>
            <tr style="height: 10px; width: 100%;">
                <td colspan="2"></td>
            </tr>
            @foreach ($sections as $sectionName => $sectionRows)
                <tr valign="top">
                    <td>
                        <span style="font-size: 18px;">
                            {{ $sectionName }}
                        </span>
                    </td>
                    <td>
                        <table width="100%" class="table" border="0">
                            <tr>
                                <th align="left">Name</th>
                                <th align="right">Total ({{ $currency }})</th>
                                <th align="right">Prev. Month ({{ $currency }})</th>
                                <th align="right">Change</th>
                            </tr>

                            @foreach ($sectionRows as $sectionRow)
                                <tr @if($sectionRow['name'] == 'All')style="background-color: #eaeaea;"@endif class="row">
                                    <td>{{ $sectionRow['name'] }} @if(isset($sectionRow['is_new']) && $sectionRow['is_new'])<span style="color: red;">(new)</span>@endif</td>
                                    <td>{{ number_format((float) $sectionRow['total_current_month']) }}</td>
                                    <td>{{ number_format((float) $sectionRow['total_previous_month']) }}</td>
                                    <td style="color: {{ $sectionRow['change_color'] }};">{{ $sectionRow['change'] }}{{$sectionRow['change'] == '-' ? '' : '%'}}</td>
                                </tr>
                            @endforeach
                        </table>
                    </td>
                </tr>
                <tr style="height: 10px; width: 100%;">
                    <td colspan="2"></td>
                </tr>
            @endforeach
        </table>
    </div>
</body>
</html>
