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
            padding-right: 40px;
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
        .notifier {
            position: relative;
            display: inline-block;
            
        }
        
        .bell {
            font-size: 26px;
            color: #FFF;
            transition: 0.3s;
        }
        
        .bell:hover {
            color: #EF476F;
        }
        
        .badge {
            position: absolute;
            top: -2px;
            left: 24px;
            padding: 0 5px;
            padding-bottom: 4px;
            font-size: 12px;
            line-height: 22px;
            height: 18px;
            background: #e90202;
            color: #FFF;
            border-radius: 10px;
            white-space: nowrap;
        }
        
        .notifier.green .badge {
            background: #06D6A0;
        }
        
        .notifier.green .bell:hover {
            color: #06D6A0;
        }
        
        .notifier.new .badge {
            animation: pulse 2s ease-out;
            animation-iteration-count: infinite;
        }
        
        @keyframes pulse {
            40% {
                transform: scale3d(1, 1, 1);
            }
            50% {
                transform: scale3d(1.2, 1.2, 1.2);
            }
            55% {
                transform: scale3d(1, 1, 1);
            }
            60% {
                transform: scale3d(1.2, 1.2, 1.2);
            }
            65% {
                transform: scale3d(1, 1, 1);
            }
        }
    </style>
</head>
<body> 
    <div class="navbar">
        <a class="active" href="#home">Dashboard</a>
        <a href="#news">News</a>
        <a href="#contact">Contact</a>
        <a href="#about">About</a>
        @php
            $nilai=[]
        @endphp
        @php
        $unreadCount = 0; // Inisialisasi penghitung untuk notifikasi yang belum dibaca
        @endphp
        
        @foreach ($datas as $notif)
            @php
                if ($notif->read_status == 0) {
                    $unreadCount++; // Menambahkan penghitung jika read_status sama dengan 1
                }
                $nilai[] = $notif->read_status;  
            @endphp
        @endforeach
        
        @if(in_array(0, $nilai))
            <a href="{{ route('newletterlist') }}" style="float: right;">
                <div class="notifier new" style="float: right;">
                    <i class="fa fa-bell"></i>
                    <div class="badge">{{ $unreadCount }}</div> {{-- Menampilkan jumlah unread count --}}
                </div>
            </a>
        
        @else
        <a href="#" style="float: right;"><i class="fa fa-bell-o" aria-hidden="true"></i>

        @endif

        <a style="float: right;" href="{{ route('logout') }}">Logout</a>
    </div>

    <!-- Content -->
    <div style="padding: 20px;">
        <!-- Isi konten dashboard di sini -->
        <h2>Selamat datang di Dashboard</h2>
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
                @foreach ($datas as $letter)
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
        
        <a href="{{ route('logout') }}">
            <button>logout</button>
        </a>
        <a href="{{ route('letters.tambahsurat') }}">
            <button>Tambah Surat</button>
        </a> 
    </div>
</body>
</html>
