<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Update Letter</title>
</head>
<body>
    <div class="container">
        <h2>Update a Letter</h2>
        @php
            $user = auth()->user()->type;
            $type = $user.".";
        @endphp
        <form action="{{ route($type.'letters.store3',['id'=>$data->id]) }}" method="post" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div>
                <label for="reference_number">Reference Number:</label>
                <input type="text" id="reference_number" name="reference_number" value="{{ $data->reference_number }}">
                @error('reference_number')
                    <div>{{ $message }}</div>
                @enderror
            </div>
            <div>
                <label for="letters_type">Letters Type:</label>
                <input type="text" id="letters_type" name="letters_type" value="{{ $data->letters_type}}">
                @error('letters_type')
                    <div>{{ $message }}</div>
                @enderror
            </div>
            <div>
                <label for="letter_date">Letter Date:</label>
                <input type="date" id="letter_date" name="letter_date" value="{{ $data->letter_date}}">
                @error('letter_date')
                    <div>{{ $message }}</div>
                @enderror
            </div>
            <div>
                <label for="received_date">Received Date:</label>
                <input type="date" id="received_date" name="received_date" value="{{ $data->received_date }}">
                @error('received_date')
                    <div>{{ $message }}</div>
                @enderror
            </div>
            <div>
                <label for="description">Description:</label>
                <textarea id="description" name="description">{{$data->description}}</textarea>
                @error('description')
                    <div>{{ $message }}</div>
                @enderror
            </div>
            <div>
                <label for="from">Pengirim:</label>
                <input type="text" id="from" name="from" value="{{$data->from}}">
                @error('Pengirim')
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
                <button type="submit">Submit</button>
                
            </div>
        </form>
    </div>
</body>
</html>
