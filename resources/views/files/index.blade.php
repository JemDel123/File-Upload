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
                    <button class="btn btn-primary" id="addNewButton">
                        <i class="fa fa-plus"></i><strong> Add New</strong>
                    </button>
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
                                <img src="{{ asset($file->updload_file) }}" class="file-preview" alt="Image" style="cursor: pointer;">
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

                                <button type="button" class="btn btn-success btn-sm edit-btn" data-id="{{ $file->id }}">
                                    <i class="fa fa-pencil"></i><strong> Edit</strong>
                                </button>

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
        <div id="modalContent"></div>
    </div>
</div>

<div id="addNewFileModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
        <h5 class="modal-title" style="text-align: center; font-weight: bold;">ADD NEW FILE</h5>
        </div>
        <div id="addNewFileModalContent">
            <form action="{{ route('files.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="mb-3">
                    <label for="detail" class="form-label">Detail</label>
                    <textarea class="form-control" id="detail" name="detail"></textarea>
                </div>
                <div class="mb-3">
                    <label for="updload_file" class="form-label">Upload File</label>
                    <input type="file" class="form-control" id="updload_file" name="updload_file">
                </div>
                <button type="submit" class="btn btn-success btn-sm">
                    <i class="fa fa-save"></i><strong> Save</strong>
                </button>
            </form>
        </div>
    </div>
</div>

<div id="editFileModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
        <h5 class="modal-title" style="text-align: center; font-weight: bold;">EDIT FILE</h5>
        </div>
        <div id="editFileModalContent">
        </div>
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

        // New elements for the "Add New" modal
        const addNewButton = document.getElementById('addNewButton');
        const addNewFileModal = document.getElementById('addNewFileModal');
        const closeAddNewModalBtn = document.getElementById('closeAddNewModal');

        // New elements for the "Edit" modal
        const editButtons = document.querySelectorAll('.edit-btn');
        const editFileModal = document.getElementById('editFileModal');
        const closeEditModalBtn = document.getElementById('closeEditModal');
        const editFileModalContent = document.getElementById('editFileModalContent');

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
                        attachViewButtonListeners();
                        attachEditButtonListeners(); // Re-attach listeners after content update
                        attachImagePopupListeners(); // Re-attach image popup listeners
                    }).catch(error => console.error('Error:', error));
            });
        }

        function attachViewButtonListeners() {
            document.querySelectorAll('.view-btn').forEach(button => {
                button.removeEventListener('click', showFileModal);
                button.addEventListener('click', showFileModal);
            });
        }

        function showFileModal() {
            const fileId = this.dataset.id;
            fetch(`/files/${fileId}`)
                .then(response => response.text())
                .then(html => {
                    modalContent.innerHTML = html;
                    modal.style.display = 'block';
                }).catch(error => console.error('Error:', error));
        }

        function attachImagePopupListeners() {
            document.querySelectorAll('.file-preview').forEach(img => {
                img.style.cursor = 'pointer'; // Indicate it's clickable
                img.removeEventListener('click', showImagePopup);
                img.addEventListener('click', showImagePopup);
            });
        }

        function showImagePopup() {
            const imageUrl = this.src; // Get the source of the clicked image
            modalContent.innerHTML = `<img src="${imageUrl}" alt="Full Size Image" style="max-width: 100%; max-height: 80vh; display: block; margin: 0 auto;">`;
            modal.style.display = 'block';
        }

        attachViewButtonListeners();
        attachImagePopupListeners(); // Attach image popup listeners on initial load

        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                modal.style.display = 'none';
            });
        }

        window.addEventListener('click', function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
            if (event.target == addNewFileModal) {
                addNewFileModal.style.display = 'none';
            }
            if (event.target == editFileModal) {
                editFileModal.style.display = 'none';
            }
        });

        // JavaScript for the "Add New" modal
        if (addNewButton) {
            addNewButton.addEventListener('click', function(event) {
                event.preventDefault(); // Prevent default link behavior
                addNewFileModal.style.display = 'block';
            });
        }

        if (closeAddNewModalBtn) {
            closeAddNewModalBtn.addEventListener('click', function(event) {
                event.preventDefault(); // Prevent potential link behavior
                addNewFileModal.style.display = 'none';
            });
        }

        // JavaScript for the "Edit" modal
        function attachEditButtonListeners() {
            document.querySelectorAll('.edit-btn').forEach(button => {
                button.removeEventListener('click', showEditModal);
                button.addEventListener('click', showEditModal);
            });
        }

        function showEditModal() {
            const fileId = this.dataset.id;
            fetch(`/files/${fileId}/edit`)
                .then(response => response.text())
                .then(html => {
                    editFileModalContent.innerHTML = html;
                    editFileModal.style.display = 'block';

                    // Add event listener for the close button inside the edit modal
                    const closeEditButtonInside = document.getElementById('closeEditModal');
                    if (closeEditButtonInside) {
                        closeEditButtonInside.addEventListener('click', function(event) {
                            event.preventDefault();
                            editFileModal.style.display = 'none';
                        });
                    }

                    // Add event listener for the form submission inside the edit modal
                    const editForm = editFileModalContent.querySelector('form');
                    if (editForm) {
                        editForm.addEventListener('submit', function(event) {
                            event.preventDefault();
                            const formData = new FormData(this);
                            const actionUrl = this.getAttribute('action');

                            fetch(actionUrl, {
                                method: 'POST',
                                body: formData,
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                }
                            }).then(response => response.json())
                              .then(data => {
                                  if (data.success) {
                                      alert(data.success);
                                      editFileModal.style.display = 'none';
                                      // Reload the page or update the file list dynamically
                                      window.location.reload();
                                  } else if (data.errors) {
                                      // Display validation errors
                                      if (data.errors.name) {
                                          editFileModalContent.querySelector('.error-name').textContent = data.errors.name[0];
                                      }
                                      if (data.errors.detail) {
                                          editFileModalContent.querySelector('.error-detail').textContent = data.errors.detail[0];
                                      }
                                      alert('Please correct the errors in the form.');
                                  } else {
                                      alert('Something went wrong.');
                                  }
                              }).catch(error => {
                                  console.error('Error:', error);
                                  alert('Error updating file.');
                              });
                        });
                    }
                }).catch(error => console.error('Error:', error));
        }

        attachEditButtonListeners();

        if (closeEditModalBtn) {
            closeEditModalBtn.addEventListener('click', function(event) {
                event.preventDefault();
                editFileModal.style.display = 'none';
            });
        }
    });
</script>   
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>