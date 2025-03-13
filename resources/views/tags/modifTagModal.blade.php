<div class="modal fade" id="tagModal-{{ $id }}" tabindex="-1" role="dialog" aria-labelledby="tagModalLabel-{{ $id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tagModalLabel-{{ $id }}">{{ trans('lang.editTag') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Form to edit an existing tag -->
                <form  method="POST">
                    @csrf
                    @method('PUT') <!-- Specify PUT method for update -->
                    <div class="form-group">
                        <label for="name">{{ trans('lang.tag_name') }}</label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="name" 
                            name="name" 
                            placeholder="{{ trans('lang.tag_name_placeholder') }}" 
                            value="{{ $tag->name ?? 'N/A' }}" 
                            required>
                    </div>
                    <div class="form-group">
                        <label for="speciality">{{ trans('lang.speciality') }}</label>
                        <select class="form-control" id="speciality" name="speciality" required>
                            <option value="">{{ trans('lang.select_speciality') }}</option>
                            <!-- Loop through specialities -->
                           
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
