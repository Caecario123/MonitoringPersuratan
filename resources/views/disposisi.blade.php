<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Add Letter</title>
</head>
<body>
    <div class="container">
        <h2>Add New Letter</h2>
        @php
        $user = auth()->user()->type;
        $type = $user.".";
        @endphp
        <form action="{{ route($type.'letters.disposisikan',['id'=>$data->id]) }}" method="post">
            @csrf
            @method('PUT')
            <p>surat nomor:{{ $data->reference_number}}</p>
            <div>
                <label for="letters_type">Jenis Surat:</label>
                <input type="text" id="letters_type" name="letters_type" value="{{ $data->letters_type}}">
                @error('letters_type')
                    <div>{{ $message }}</div>
                @enderror
            </div>
            <div>
                <label for="description">Perihal:</label>
                <textarea id="description" name="description" >{{ $data->description}}</textarea>
                @error('description')
                    <div>{{ $message }}</div>
                @enderror
            </div>
            <div>
                <label for="disposition_date">Tanggal Disposisi</label>
                <input type="date" id="disposition_date" name="disposition_date" value="{{ $data->letter_date}}">
                @error('disposition_date')
                    <div>{{ $message }}</div>
                @enderror
            </div>
            <div>
                <label for="disposition_note">Note Disposisi:</label>
                <textarea id="disposition_note" name="disposition_note" >{{ $data->description}}</textarea>
                @error('disposition_note')
                    <div>{{ $message }}</div>
                @enderror
            </div>
            <div>
                <label for="disposition_process">Tindaklanjut Disposisi:</label>
                <select id="disposition_process" name="disposition_process">
                    <option value="Tata Usaha">Tata Usaha</option>
                    <option value="Seksi penetapan hak dan pendaftaran">Seksi penetapan hak dan pendaftaran </option>
                    <option value="Seksi survei dan pemetaan">Seksi survei dan pemetaan</option>
                    <option value="Seksi penataan dan pemberdayaan">Seksi penataan dan pemberdayaan</option>
                    <option value="Seksi pengadaan tanah dan pengembangan">Seksi pengadaan tanah dan pengembangan</option>
                    <option value="Seksi pengendalian dan penanganan sengketa">Seksi pengendalian dan penanganan sengketa</option>
                </select>
                @error('disposition_process')
                    <div>{{ $message }}</div>
                @enderror
            </div>       
            <div>
                <button type="submit">Submit</button>
            </div>
        </form>
    </div>
</body>
</html>
