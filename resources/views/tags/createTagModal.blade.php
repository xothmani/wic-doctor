
<div class="modal fade" id="createTagModal" tabindex="-1" role="dialog" aria-labelledby="createTagModalLabel" aria-hidden="true">
    <div class="modal-dialog d-flex justify-content-center align-items-center" role="document" style="min-height: 100vh;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createTagModalLabel">{{ trans('lang.tag_create') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Form to create a new tag -->
                <form action="{{ route('tags.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="name">{{ trans('lang.tag_name') }}</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="{{ trans('lang.tag_name_placeholder') }}" required>
                    </div>
                    <div class="form-group">
                        <label for="speciality">{{ trans('lang.speciality') }}</label>
                        <select class="form-control" id="speciality" name="speciality" required>
                            <option value="">{{ trans('lang.select_speciality') }}</option>
                            @foreach($specialities as $speciality)
                                <option value="{{ $speciality->id }}">{{ $speciality->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('lang.close') }}</button>
                        <button type="submit" class="btn bg-{{setting('theme_color')}}">{{ trans('lang.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
