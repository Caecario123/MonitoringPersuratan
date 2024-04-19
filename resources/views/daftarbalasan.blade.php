<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <title>Document</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        .fa-file {
            font-size: 24px;
        }
        button {
            padding: 5px 10px;
            border: none;
            background-color: #007bff;
            color: white;
            cursor: pointer;
            border-radius: 4px;
        }

        button:hover {
            background-color: #0056b3;
        }

    </style>
</head>
<body> 
    <div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nomor Surat Keluar</th>
                    <th>Tanggal Surat Keluar</th>
                    <th>Keterangan</th>
                    <th>File</th>
                    <th>aksi</th>

                </tr>
            </thead>
            <tbody>
                @foreach ($outgoingLetter as $balasan)
                <tr>
                    <td>{{ $balasan->id }}</td>
                    <td>{{ $balasan->reference_number2 }}</td>
                    <td>{{ $balasan->outgoing_letter_date }}</td>
                    <td>{{ $balasan->note }}</td>
                    <td>
                        @php
                        $user = auth()->user()->type;
                        $type = $user.".";
                        @endphp 
                        <a href="{{ route($type.'filebalasan.streamOutgoingPDF', ['id' => $balasan->id]) }}">
                            <i class="fa fa-file" style="font-size:24px"></i>
                        </a>                  
                    </td>
                    <td><a href="{{ route($type.'deleteOutgoingLetter',['id'=>$balasan->id]) }}">
                        Hapus
                    </a>
                    <a href="{{ route($type.'editbalasan',['id'=>$balasan->id]) }}">
                        Edit
                    </a>
                    </td>

                </tr>
                @endforeach
            </tbody>
        </table>
        
        <a href="{{ route('logout') }}">
            <button>Logout</button>
        </a>
        @php
        $user = auth()->user()->type;
        $type = $user.".";
        @endphp
        <a href="{{ route($type.'dashboard') }}">
            <button>Kembali </button>
        </a> 
    
    </div>
</body>
</html>
