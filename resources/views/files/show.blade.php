<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="{{ asset('css/show.css') }}">
</head>
<body>

<div class="container" style="max-width: 600px; margin: 50px auto;">
    <div class="card">
        <div class="card-header"><h4>Image View</h4></div>
        <div class="card-body">
            <a href="{{ route('files.index') }}" class="btn btn-info btn-nm"><i class="fa fa-arrow-left"></i> Back</a>

            <div class="mt-4">
                @if ($file->updload_file)
                    @php
                        $extension = strtolower(pathinfo($file->updload_file, PATHINFO_EXTENSION));
                    @endphp
                    @if (in_array($extension, ['png', 'jpg', 'jpeg', 'webp']))
                        <p><strong>Name: </strong>{{ $file->name }}</p>
                        <p><strong>Description: </strong>{{ $file->detail }}</p>
                        <img src="{{ asset($file->updload_file) }}" style="max-width:100%; height:auto;" alt="Image">
                    @elseif (in_array($extension, ['pdf', 'doc', 'docx']))
                        <a href="{{ asset($file->updload_file) }}" target="_blank" class="btn btn-primary btn-sm">
                            <i class="fa fa-eye"></i><strong> View</strong>
                        </a>
                    @else
                        Unsupported file type
                    @endif
                @else
                    No file uploaded
                @endif
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>