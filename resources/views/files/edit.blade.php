<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Edit</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/create.css') }}">
</head>
<body>
    <div class="container mt-4">
        <div class="card">
            <div class="card-header"><h4><strong>Edit File</strong></h4></div>
            <div class="card-body">
                <a href="{{ route('files.index') }}" class="btn btn-info btn-sm mb-3">
                    <i class="fa fa-arrow-left"></i><strong> Back</strong>
                </a>

                <form action="{{ route('files.update', $file->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="name" class="form-label"><strong>Name</strong></label>
                        <input type="text" name="name" id="name" placeholder="Name" class="form-control" value="{{ $file->name }}" required>
                        @error("name")
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="detail" class="form-label"><strong>Description</strong></label>
                        <textarea name="detail" id="detail" placeholder="Description" class="form-control" rows="4" required>{{ $file->detail }}</textarea>
                        @error("detail")
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="updload_file" class="form-label"><strong>Upload File/Image</strong></label>
                        <input type="file" name="updload_file" id="updload_file" class="form-control" placeholder="Choose a file...">
                        <small class="form-text text-muted">Current file: {{ basename($file->updload_file) }}</small>
                    </div>

                    <div class="mt-2">
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="fa fa-save"></i><strong> Save</strong>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>