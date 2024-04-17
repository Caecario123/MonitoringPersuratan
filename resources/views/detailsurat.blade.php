<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Surat</title>
</head>
<body>
    <h1>Detail Surat</h1>

    <div>
        <h2>Informasi Surat</h2>
        <ul>
            <li><strong>ID:</strong> {{ $letter->id }}</li>
            <li><strong>Tanggal Penerima:</strong> {{ $letter->received_date }}</li>
            <li><strong>Jenis Surat:</strong> {{ $letter->letters_type }}</li>
            <li><strong>Nomor Surat:</strong> {{ $letter->reference_number }}</li>
            <li><strong>Tanggal Surat:</strong> {{ $letter->letter_date }}</li>
            <li><strong>Pengirim:</strong> {{ $letter->from }}</li>
            <li><strong>Perihal:</strong> {{ $letter->description }}</li>
            <!-- Tambahkan informasi lainnya sesuai kebutuhan -->
        </ul>
    </div>

    <div>
        <h2>Disposisi Surat</h2>
        <ul>
            <li><strong>Tanggal Disposisi:</strong> {{ $letter->disposition_date }}</li>
            <li><strong>Catatan Disposisi:</strong> {{ $letter->disposition_note }}</li>
            <li><strong>Tindak Lanjut Kasubag:</strong> {{ $letter->disposition_process }}</li>
            <!-- Tambahkan informasi lainnya sesuai kebutuhan -->
        </ul>
    </div>

    <div>
        <h2>Tindakan</h2>
        <ul>
            @php
            $user = auth()->user()->type;
            $type = $user.".";
            @endphp
            <li><a href="{{ route($type.'letters.balasan',['id'=>$letter->id]) }}">Balas Surat</a></li>
            <!-- Tambahkan tindakan lainnya sesuai kebutuhan -->
        </ul>
    </div>

    <div>
        <h2>File Surat</h2>
        <ul>
            <li><a href="{{ route('file.streamPDF', ['id' => $letter->id]) }}">Lihat Surat (PDF)</a></li>
            <!-- Tambahkan tautan ke file surat lainnya sesuai kebutuhan -->
        </ul>
    </div>

    <a href="{{ route('dashboard') }}">Kembali ke Dashboard</a>
</body>
</html>
