<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Предпросмотр перечня</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: "Times New Roman", serif;
            font-size: 12px;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 100%;
            background: white;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .title {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 20px;
            text-align: center;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin: 20px 0;
            font-size: 12px;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px 8px;
            text-align: left;
            font-size: 12px;
        }
        th {
            background-color: #d3d3d3;
            font-weight: bold;
        }
        td {
            background-color: #fff;
        }
        tr:nth-child(even) td {
            background-color: #f9f9f9;
        }
        .controls {
            margin-bottom: 20px;
            text-align: right;
        }
        .btn {
            padding: 10px 20px;
            margin-left: 10px;
            background-color: #FFB800;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
        }
        .btn:hover {
            background-color: #E6A600;
        }
        @media print {
            body {
                background-color: white;
                margin: 0;
            }
            .controls {
                display: none;
            }
            .container {
                box-shadow: none;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="controls">
            <button class="btn" onclick="window.print()">Печать</button>
            <button class="btn" onclick="window.close()">Закрыть</button>
        </div>
        <div class="title">формировать перечень (ВЫГРУЗИТЬ ВОРД)</div>
        <table>
            <thead>
                <tr>
                    <th>№ п/п (шифр)</th>
                    <th>Наименование разработки</th>
                    <th>Стоимость всего</th>
                    <th>Стоимость на 2025</th>
                    <th>Стоимость на 2026</th>
                    <th>Срок начала разработки (месяц, год)</th>
                    <th>Срок окончания разработки (месяц, год)</th>
                    <th>Наименование организаций, выполняющих работу, и номер Технического Комитета</th>
                    <th>Ответственный отдел</th>
                    <th>Разрабатывается впервые или взамен действующих нормативных документов</th>
                </tr>
            </thead>
            <tbody>
                @foreach($grouped as $name => $groupItems)
                    @foreach($groupItems as $item)
                        <tr>
                            <td>{{ $item->code ?? '' }}</td>
                            <td>{{ $item->development_name ?? '' }}</td>
                            <td>{{ $item->total_cost ? number_format($item->total_cost, 0, ',', ' ') : '' }}</td>
                            <td>{{ $item->cost_2025 ? number_format($item->cost_2025, 0, ',', ' ') : '' }}</td>
                            <td>{{ $item->cost_2026 ? number_format($item->cost_2026, 0, ',', ' ') : '' }}</td>
                            <td>{{ $formatDate($item->development_start ?? $item->start_date) }}</td>
                            <td>{{ $formatDate($item->development_end ?? $item->end_date) }}</td>
                            <td>{{ $item->organizations ?? '' }}</td>
                            <td>{{ $item->department ?? '' }}</td>
                            <td>{{ $item->development_type ?? '' }}</td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>

