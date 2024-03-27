<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta http-equiv="refresh" content="30" > 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <title>Dashboard</title>
    
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

        .fa-file {
            font-size: 24px;
        }

        .fa-file.red {
            color: red;
        }
         /* CSS untuk Navbar */
         .navbar {
            overflow: hidden;
            background-color: #333;
        }

        .navbar a {
            float: left;
            display: block;
            color: #f2f2f2;
            text-align: center;
            padding: 14px 20px;
            text-decoration: none;
        }

        .navbar a:hover {
            background-color: #ddd;
            color: black;
        }

        .navbar a.active {
            background-color: #007bff;
            color: white;
        }
    </style>
</head>
<body> 
   
    <!-- Content -->
    <div style="padding: 20px;">
        <!-- Isi konten dashboard di sini -->
        <h2>Daftar surat masuk</h2>
        <!-- Tambahan konten lainnya -->
    </div>
    <div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tanggal Penerima</th>
                    <th>Jenis Surat</th>
                    <th>Nomor Surat</th>
                    <th>Tanggal Surat</th>
                    <th>Pengirim</th>
                    <th>Perihal</th>
                    <th>Tanggal Disposisi</th>
                    <th>Catatan Disposisi</th>
                    <th>Tindak Lanjut Kasubag</th>
                    <th>Status</th>
                    <th>User ID</th>
                    <th>Tindakan</th>
                    <th>File</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $letter)
                    <tr>

                        <td><a href="{{ route('letters.detailSurat',['id'=>$letter->id]) }}">
                            {{ $letter->id }}</a>
                        </td>

                        <td>{{ $letter->received_date }}</td>
                        <td>{{ $letter->letters_type }}</td>
                        <td>{{ $letter->reference_number }}</td>
                        <td>{{ $letter->letter_date }}</td>
                        <td>{{ $letter->from }}</td>
                        <td>{{ $letter->description }}</td>
                        <td>{{ $letter->disposition_date }}</td>
                        <td>{{ $letter->disposition_note }}</td>
                        <td>
                            @if ($letter->disposition_process == "")
                                <a href="{{ route('letters.disposisi',['id'=>$letter->id]) }}">
                                    <button>Belum di disposisi</button>
                                </a>
                            @endif
                            <a href="{{ route('letters.disposisi',['id'=>$letter->id]) }}">
                                {{ $letter->disposition_process }}
                            </a>
                        </td>
                        <td>{{ $letter->status }}</td>
                        <td>{{ $letter->user_id }}</td>
                        <td><a href="{{ route('letters.balasan',['id'=>$letter->id]) }}">
                            @if ($letter->disposition_process != "Belum ditindak lanjuti")
                            balas
                            @endif
                            </a>
                        </td>
                        <td>
                            <a href="{{ route('file.streamPDF', ['id' => $letter->id]) }}">
                                <i class="fa fa-file" style="font-size:24px"></i>
                            </a>
                            
                            @if ($letter->status == "Complete")
                            <a href="{{ route('daftarbalasan',['id'=>$letter->id]) }}">
                                <i class="fa fa-file" style="font-size:24px;color:red"></i>
                            </a>
                        @endif
                        </td>
                    </tr>
                
                @endforeach
            </tbody>
        </table>
        
        <a href="{{ route('dashboard') }}">
            <button>Kembali ke Dashboard</button>
        </a>

        <a href="{{ route('letters.tambahsurat') }}">
            <button>Tambah Surat</button>
        </a> 
    </div>
</body>
</html>
