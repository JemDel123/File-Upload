<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Files List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/file-list.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
<div class="container mt-4">
        <div class="main-container">
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="search-bar">
                        <input type="text" id="searchInput" placeholder="Search files...">
                        <button id="searchButton"><strong>Search</strong></button>
                        <a href="{{ route('files.create') }}" class="btn btn-primary" id="addNewButton">
                            <i class="fa fa-plus"></i><strong> Add New</strong>
                        </a>
                        <div class="user-dropdown">
                            <button class="user-dropdown-toggle" id="userDropdownToggle">
                                <i class="fa fa-user user-icon"></i>
                                @auth
                                    {{ Auth::user()->name }}
                                @else
                                    Guest
                                @endauth
                            </button>
                            <div class="user-dropdown-menu" id="userDropdownMenu">
                                <button class="user-dropdown-item" id="logoutButton">
                                    <i class="fa fa-sign-out"></i> Logout
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if(session("success"))
                <div class="alert alert-success">{{ session("success") }}</div>
            @endif

            <div class="content-container">
                <ul class="file-list">
                    @foreach($files as $file)
                        @php
                            $extension = strtolower(pathinfo($file->updload_file, PATHINFO_EXTENSION));
                        @endphp
                        <li class="file-item">
                            <div class="d-flex align-items-center file-info">
                                @if ($file->updload_file && in_array($extension, ['png', 'jpg', 'jpeg', 'webp']))
                                    <img src="{{ asset($file->updload_file) }}" class="file-preview" alt="Image">
                                @endif
                                <div>
                                    <h4 class="file-name">{{ $file->name }}</h4>
                                    <p class="file-detail">{{ $file->detail }}</p>
                                    @if ($file->updload_file && in_array($extension, ['pdf', 'doc', 'docx']))
                                        <p><strong>File:</strong> {{ basename($file->updload_file) }}</p>
                                    @elseif(!$file->updload_file)
                                        <p>No file uploaded</p>
                                    @else
                                        <p>Unsupported file type</p>
                                    @endif
                                </div>
                            </div>
                            <div class="file-actions">
                                <form action="{{ route('files.destroy', $file->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')

                                    @if ($file->updload_file)
                                        @if (in_array($extension, ['png', 'jpg', 'jpeg', 'webp']))
                                            <button type="button" class="btn btn-primary btn-sm view-btn" data-id="{{ $file->id }}">
                                                <i class="fa fa-eye"></i><strong> View</strong>
                                            </button>
                                        @elseif ($extension === 'pdf')
                                            <a href="{{ asset($file->updload_file) }}" target="_blank" class="btn btn-danger btn-sm">
                                                <i class="fa fa-eye"></i><strong> View PDF</strong>
                                            </a>
                                        @elseif (in_array($extension, ['doc', 'docx']))
                                            <a href="{{ asset($file->updload_file) }}" download="{{ basename($file->updload_file) }}" class="btn btn-info btn-sm">
                                                <i class="fa fa-download"></i><strong> Download</strong>
                                            </a>
                                        @endif
                                    @endif

                                    <a href="{{ route('files.edit', $file->id) }}" class="btn btn-success btn-sm edit-btn" data-id="{{ $file->id }}">
                                        <i class="fa fa-pencil"></i><strong> Edit</strong>
                                    </a>

                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fa fa-trash"></i><strong> Delete</strong>
                                    </button>
                                </form>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="pagination-container mt-4">
                {{ $files->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

    <div id="fileModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div id="modalContent"></div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const logoutButton = document.getElementById('logoutButton');
            const userDropdownToggle = document.getElementById('userDropdownToggle');
            const userDropdownMenu = document.getElementById('userDropdownMenu');
            const searchButton = document.getElementById('searchButton');
            const modal = document.getElementById('fileModal');
            const modalContent = document.getElementById('modalContent');
            const closeBtn = document.querySelector('.close');

            if (logoutButton) {
                logoutButton.addEventListener('click', function() {
                    fetch('/logout', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json'
                        },
                        credentials: 'same-origin'
                    }).then(response => {
                        if (response.ok) {
                            window.location.href = '/login';
                        } else {
                            console.error('Logout failed');
                        }
                    }).catch(error => console.error('Error:', error));
                });
            }

            if (userDropdownToggle) {
                userDropdownToggle.addEventListener('click', function() {
                    userDropdownMenu.classList.toggle('show');
                });
                window.addEventListener('click', function(event) {
                    if (!userDropdownMenu.contains(event.target) && event.target !== userDropdownToggle) {
                        userDropdownMenu.classList.remove('show');
                    }
                });
            }

            if (searchButton) {
                searchButton.addEventListener('click', function() {
                    const searchTerm = document.getElementById('searchInput').value;
                    fetch(`/files?search=${encodeURIComponent(searchTerm)}`)
                        .then(response => response.text())
                        .then(html => {
                            const tempDiv = document.createElement('div');
                            tempDiv.innerHTML = html;
                            document.querySelector('.file-list').innerHTML = tempDiv.querySelector('.file-list').innerHTML;
                            document.querySelector('.pagination-container').innerHTML = tempDiv.querySelector('.pagination-container').innerHTML;
                        }).catch(error => console.error('Error:', error));
                });
            }

            document.querySelectorAll('.view-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const fileId = this.dataset.id;
                    fetch(`/files/${fileId}`)
                        .then(response => response.text())
                        .then(html => {
                            modalContent.innerHTML = html;
                            modal.style.display = 'block';
                        }).catch(error => console.error('Error:', error));
                });
            });

            if (closeBtn) {
                closeBtn.addEventListener('click', function() {
                    modal.style.display = 'none';
                });
            }

            window.addEventListener('click', function(event) {
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>