<!-- admin/js/form.php -->

<script>
    // Script for form input (if invalid input give hint)
    document.addEventListener('input', async e => {
        const input = e.target;
        const type = input.dataset.validate; // data-validate (username, email...)
        if (!type) return;

        const form = input.closest('form');
        const mode = form.dataset.mode; //  data-mode form mode create / edit
        const hint = input.nextElementSibling; 
        const value = input.value.trim();

        // EDIT MODE: unchanged field -> skip validation
        if (mode === 'edit' &&
            input.dataset.original !== undefined && // if user didnt modify the data
            value === input.dataset.original) {

            input.classList.remove('valid', 'invalid');
            hint.textContent = '';
            checkForm();
            return;
        }

        if (!value) {
            input.classList.remove('valid', 'invalid');
            hint.textContent = '';
            checkForm();
            return;
        }

        if (type === 'password') {
            if (mode === 'edit' && value === '') {
                input.classList.remove('valid', 'invalid');
                hint.textContent = '';
            } else if (value.length < 8) {
                setInvalid(input, hint, 'Minimum 8 characters');
            } else {
                setValid(input, hint);
            }
            checkForm();
            return;
        }

        const entity = document.querySelector('input[name="entity"]').value;
        const id = document.querySelector('input[name="id"]')?.value ?? '';

        const res = await fetch(
            `/APU-SustainaQuest/admin/process/validate.php?type=${type}&entity=${entity}&id=${id}&value=${encodeURIComponent(value)}`
        );

        const data = await res.json();

        data.valid ? setValid(input, hint) : setInvalid(input, hint, data.message);
        checkForm();
    });

    // For the data without data-validate
    document.addEventListener('change', e => {
        const form = e.target.closest('form[data-mode="edit"]');
        if (!form) return;

        checkForm();
    });

    function setValid(input, hint) {
        input.classList.add('valid');
        input.classList.remove('invalid');
        hint.textContent = '';
    }

    function setInvalid(input, hint, msg) {
        input.classList.add('invalid');
        input.classList.remove('valid');
        hint.textContent = msg;
    }

    function checkForm() {
        const form = document.querySelector('form[data-mode]');
        if (!form) return;
        
        const submit = form.querySelector('button[type="submit"]');
        const mode = form.dataset.mode; // edit or create


        let hasInvalid = false;
        let hasChange = false;

        form.querySelectorAll('input').forEach(input => {

            // invalid check
            if (input.classList.contains('invalid')) {
                hasInvalid = true;
            }

            //Check for Edit, check if they change any data or not, if yes enable update button to click
            if (mode !== 'edit') return;

            const original = input.dataset.original;

            // PASSWORD: any input enables update
            if (input.type === 'password') {
                if (input.value.trim() !== '') {
                    hasChange = true;
                }
                return;
            }

            // Radio
            if (input.type === 'radio') {
                if (input.checked && original !== undefined && input.value !== original) {
                    hasChange = true;
                }
                return;
            }

            // NORMAL INPUT (text / number / email)
            if (original !== undefined && input.value !== original) {
                hasChange = true;
            }

            // file
            if (input.type === 'file') {
                if (input.files && input.files.length > 0) {
                    hasChange = true;
                }
                return;
            }
        });

        /* =========================
            TEXTAREA (Check if they edit text area)
           ========================= */
        form.querySelectorAll('textarea').forEach(textarea => {
            if (mode !== 'edit') return;

            const original = textarea.dataset.original ?? '';

            if (textarea.value !== original) {
                hasChange = true;
            }
        });


        if (mode === 'create') {
            let empty = false;

            // Required inputs (text, number, email)
            form.querySelectorAll('input[required]:not([type="radio"]):not([type="file"])')
                .forEach(i => {
                    if (!i.value.trim()) empty = true;
                });

            // Required textarea
            form.querySelectorAll('textarea[required]')
                .forEach(t => {
                    if (!t.value.trim()) empty = true;
                });

            // Required file
            form.querySelectorAll('input[type="file"][required]')
                .forEach(f => {
                    if (!f.files || f.files.length === 0) empty = true;
                });

            // Required radio groups
            const radioNames = new Set(
                [...form.querySelectorAll('input[type="radio"][required]')].map(r => r.name)
            );

            radioNames.forEach(name => {
                if (!form.querySelector(`input[type="radio"][name="${name}"]:checked`)) {
                    empty = true;
                }
            });

            submit.disabled = hasInvalid || empty;
            return;
        }

        // for edit mode
        submit.disabled = !hasChange || hasInvalid;
    }

    document.addEventListener('DOMContentLoaded', checkForm);
    document.addEventListener('input', checkForm);
    document.addEventListener('change', checkForm);

</script>

<script>
    // Click Image to change
    const fileInput = document.getElementById('icon');
    const frame = document.getElementById('imageFrame');
    const preview = document.getElementById('previewImg');

    frame.addEventListener('click', () => {
        fileInput.click();
    });

    fileInput.addEventListener('change', () => {
        const file = fileInput.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = e => {
            preview.src = e.target.result;
            preview.hidden = false;

            const placeholder = frame.querySelector('.placeholder');
            if (placeholder) placeholder.remove();
        };
        reader.readAsDataURL(file);
    });
</script>
