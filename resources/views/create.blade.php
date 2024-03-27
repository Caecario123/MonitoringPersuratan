@extends('layout.main')

@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">User</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Tambah User</li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->
    <section class="content">
      <form action=" {{ route('admin.user.store') }}" method="POST">  
            @csrf
            <div class="container-fluid">
          <div class="row">
            <!-- left column -->
            <div class="col-md-6">
              <!-- general form elements -->
              <div class="card card-primary">
                <div class="card-header">
                  <h3 class="card-title">Form tambah user</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form>
                  <div class="card-body">
                    <div class="form-group">
                      <label for="exampleInputEmail1">Email</label>
                      <input type="email" class="form-control" id="exampleInputEmail1" name="email" placeholder="Enter email">
                      @error('email')
                        <small>{{ $message }}</small>
                      @enderror
                    </div>
                    <div class="form-group">
                        <label for="exampleInputEmail1">Nama</label>
                        <input type="text" name="nama" class="form-control" id="exampleInputEmail1" placeholder="Enter Name">
                    </div>
                    @error('nama')
                        <small>{{ $message }}</small>
                    @enderror
                    <div class="form-group">
                      <label for="exampleInputPassword1">Password</label>
                      <input type="password" name="password" class="form-control" id="exampleInputPassword1" placeholder="Password">
                    </div>
                    @error('password')
                        <small>{{ $message }}</small>
                    @enderror
                  </div>
                  <div class="form-group">
                    <label for="type">Type</label>
                    <select class="form-control" id="type" name="type">
                        <option value="0">Admin</option>
                        <option value="1">Kakan</option>
                        <option value="2">Tata Usaha</option>
                        <option value="3">Seksi 1 Penetapan Hak dan Pendaftaran</option>
                        <option value="4">Seksi 2 Survei dan Pemetaan</option>
                        <option value="5">Seksi 3 Pendataan dan Pemberdayaan</option>
                        <option value="6">Seksi 4 Pengadaan Tanah dan Pengembangan</option>
                        <option value="7">Seksi 5 Pengendalian dan Penanganan Sengketa</option>
                    </select>
                  </div>
                  <!-- /.card-body -->
  
                  <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Submit</button>
                  </div>
                </form>
              </div>
              <!-- /.card -->
            </div>
          </div>
          <!-- /.row -->
            </div><!-- /.container-fluid -->
      </form>
      </section>
  </div>
  
@endsection