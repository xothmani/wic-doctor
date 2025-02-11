@extends('layouts.app')

@push('css_lib')
    <!-- select2 -->
    <link rel="stylesheet" href="{{ asset('vendor/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <!-- dropzone -->
    <link rel="stylesheet" href="{{ asset('vendor/dropzone/min/dropzone.min.css') }}">
@endpush

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Doctors Gallery <small>Manage categories & images</small></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb bg-white float-sm-right rounded-pill px-4 py-2 d-none d-md-flex">
                    <li class="breadcrumb-item">
                        <a href="{{ url('/dashboard') }}">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item active">Doctors Gallery</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="clearfix"></div>
    @include('flash::message')

    <div id="mediaModal" class="card shadow-sm">
        <div class="card-header">
            <div class="form-group">
                <label for="category">Select or Create a Category</label>
                <div class="d-flex">
                    <select name="category" id="category" class="form-control select2">
                        <option value="default">Default</option>
                    </select>
                    <button id="refreshMedia" class="btn btn-outline-primary ml-2">Refresh</button>
                </div>
                <input type="text" id="newCategory" class="form-control mt-2"
                    placeholder="New Category (if not listed)">
            </div>
        </div>

        <div class="card-body">
            <!-- Dropzone Field -->
            <div id="uploadSection">
                <button id="createMedia" class="btn btn-primary mb-3">Upload Files</button>
                <div id="createMediaField" class="row" style="display: none;">
                    <div class="col-12">
                        <div style="width: 100%" class="dropzone" id="mediaDropzone" data-field="default"></div>
                        <button id="doneMedia" class="btn btn-outline-primary btn-sm float-right mt-2">Done</button>
                        <div class="form-text text-muted">Upload your images here.</div>
                    </div>
                </div>
            </div>

            <!-- Media Items -->
            <div class="row medias-items">
                <div class="card loader">
                    <div class="overlay">
                        <i class="fas fa-redo-alt fa-spin"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts_lib')
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="{{ asset('vendor/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('vendor/dropzone/min/dropzone.min.js') }}"></script>
    <script type="text/javascript">
        Dropzone.autoDiscover = false;

        $(document).ready(function () {
            // Initialize Select2
            $('#category').select2();

            // Load Categories
            function loadCategories() {
                let select = $('#category');
                let currentCategory = select.val(); // Store the current selected value

                select.empty(); // Clear existing options
                $.ajax({
                    url: "{{ url('doctors-gallery/categories') }}",
                    method: 'GET',
                    success: function (data) {
                        const uniqueCategories = [...new Set(data)];
                        select.append('<option value="default">Default</option>'); // Always include default
                        uniqueCategories.forEach(category => {
                            select.append(`<option value="${category}">${category}</option>`);
                        });

                        if (currentCategory) {
                            select.val(currentCategory); // Retain the previously selected value
                        } else {
                            select.val('default'); // Default to 'default' if no category is selected
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error("Error loading categories:", error);
                    }
                });
            }



            // Load Media Items
            function loadMedia() {
                let category = $('#category').val() || 'default';
                let mediaItems = $('.medias-items');

                mediaItems.html('<div class="card loader"><div class="overlay"><i class="fas fa-redo-alt fa-spin"></i></div></div>');
                $.ajax({
                    url: "{{ url('doctors-gallery/all') }}/" + category,
                    method: 'GET',
                    success: function (data) {
                        let html = '';
                        data.forEach(item => {
                            html += `
                                                                                                                                                                                <div class="media-item m-2">
                                                                                                                                                                                    <div class="card">
                                                                                                                                                                                        <img class="card-img-top" src="${item.thumb}" alt="${item.file_name}">
                                                                                                                                                                                        <div class="card-footer text-center">
                                                                                                                                                                                            <small>${item.name} (${item.formated_size})</small>
                                                                                                                                                                                        </div>
                                                                                                                                                                                    </div>
                                                                                                                                                                                </div>`;
                        });
                        mediaItems.html(html);
                    },
                    error: function (xhr, status, error) {
                        console.error("Error loading media:", error);
                    }
                });
            }


            // Dropzone Initialization
            var mediaDropzone = new Dropzone('#mediaDropzone', {
                url: "{{ url('doctors-gallery/store') }}",
                addRemoveLinks: true,
                sending: function (file, xhr, formData) {
                    let selectedCategory = $('#category').val() || 'default';
                    let newCategory = $('#newCategory').val().trim();
                    let categoryToUse = newCategory || selectedCategory;
                    let uuid = file.upload.uuid; // Dropzone automatically assigns a UUID

                    formData.append('category', categoryToUse); // Append category
                    formData.append('uuid', uuid); // Append UUID
                    formData.append('_token', '{{ csrf_token() }}');
                    console.log("Sending File to Category:", categoryToUse);
                },
                success: function (file) {
                    // Add new category to dropdown if it doesn't exist
                    let newCategory = $('#newCategory').val().trim();
                    if (newCategory) {
                        let select = $('#category');
                        if (!select.find(`option[value="${newCategory}"]`).length) {
                            select.append(new Option(newCategory, newCategory));
                        }
                        select.val(newCategory); // Select the new category
                    }

                    // Automatically refresh media for the selected or new category
                    loadMedia();

                    // Clear the "new category" input
                    $('#newCategory').val('');
                },
                complete: function (file) {
                    console.log("Upload Complete for File:", file.name);
                }
            });


            // Show Dropzone
            $('#createMedia').on('click', function () {
                $('#createMediaField').show();
            });

            // Hide Dropzone
            $('#doneMedia').on('click', function () {
                $('#createMediaField').hide();
                mediaDropzone.removeAllFiles(true);
            });

            // Refresh Media
            $('#refreshMedia').on('click', function () {
                loadCategories();
                loadMedia();
            });

            // Category Change
            $('#category').on('change', function () {
                let selectedCategory = $(this).val();
                console.log("Selected Category Changed:", selectedCategory);
                loadMedia(); // Reload media for the selected category
            });

            // Initial Load
            loadCategories();
            loadMedia();
        });
    </script>
@endpush