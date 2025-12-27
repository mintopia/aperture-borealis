@extends('layouts.login')
@section('content')
    <div class="text-center">
        <p>
            Enter the code from your browser.
        </p>
        <form class="my-5" method="GET" action="{{ route('auth') }}">
            <input type="hidden" name="code" id="code" value="" />
            <div class="row g-4">
                <div class="col">
                    <div class="row g-2">
                        @for($i = 0; $i < $length; $i++)
                            <div class="col-2 @if($i === 0)offset-{{ max(0, round((12 - ($length * 2)) / 2)) }}@endif">
                                <input type="text" class="form-control form-control-lg text-center px-3 py-3" maxlength="1" data-code-input="">
                            </div>
                        @endfor
                    </div>
                </div>
            </div>
            <div class=" mt-4 row col-{{ $length * 2 }} offset-{{ max(0, round((12 - ($length * 2)) / 2)) }}">
                <button class="btn btn-primary" type="submit">Continue</button>
            </div>
        </div>
    </form>
@endsection
@push('footer-scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        let inputs = document.querySelectorAll("[data-code-input]");

        let codeInput = document.getElementById('code');
        let updateCode = function() {
            let code = '';
            for (let i = 0; i < inputs.length; i++) {
                code += inputs[i].value;
            }
            codeInput.value = code;
        }

        // Attach an event listener to each input element
        for (let i = 0; i < inputs.length; i++) {
            inputs[i].addEventListener("input", function (e) {
                // If the input field has a character, and there is a next input field, focus it
                if (e.target.value.length === e.target.maxLength && i + 1 < inputs.length) {
                    inputs[i + 1].focus();
                }
                updateCode();
            });
            inputs[i].addEventListener("keydown", function (e) {
                // If the input field is empty and the keyCode for Backspace (8) is detected, and there is a previous input field, focus it
                if (e.target.value.length === 0 && e.keyCode === 8 && i > 0) {
                    inputs[i - 1].focus();
                }
                updateCode();
            });
        }
    });
</script>
@endpush
