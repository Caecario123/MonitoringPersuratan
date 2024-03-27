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
        <h2>Balasan</h2>
        <form action="{{ route('balasansurat') }}" method="post"enctype="multipart/form-data">
            @csrf
            <p>surat nomor:{{ $data->reference_number}}</p>
            
            <div>
                <label for="reference_number2">Nomor Surat Keluar:</label>
                <input type="text" id="reference_number2" name="reference_number2" >
                @error('reference_number2')
                    <div>{{ $message }}</div>
                @enderror
            </div>
            <div>
                <label for="letter_id">id surat masuk:</label>
                <input type="text" id="letter_id" name="letter_id" value="{{ $data->id}}"></input>
                @error('letter_id')
                    <div>{{ $message }}</div>
                @enderror
            </div>
            <div>
                <label for="note">Keterangan:</label>
                <input type="text" id="note" name="note" >
                @error('note')
                    <div>{{ $message }}</div>
                @enderror
            </div>
            <div>
                <label for="user_id">user id:</label>
                <input type="text" id="user_id" name="user_id" value="{{ $data->user_id}}"></input>
            </div>   
            <div>
                <label for="outgoing_letter_date">Letter Date:</label>
                <input type="date" id="outgoing_letter_date" name="outgoing_letter_date" >
                @error('outgoing_letter_date')
                    <div>{{ $message }}</div>
                @enderror
            </div>
            <div>
                <label for="uploadfile">upload file:</label>
                <input type="file" id="file" name="file" value="{{ old('file') }}">
                @error('file')
                    <div>{{ $message }}</div>
                @enderror
            </div>  
            <div>
                <label for="status">status:</label>
                <select id="status" name="status">
                    <option value="Pending">Pending</option>
                    <option value="Complete">Complete</option>
                </select>
                @error('status')
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
