BX.ready(() => {
    setInterval(() => console.log(1), 2000)

    function handleSaveClick() {
        const contactForm = document.querySelector('.ui-form.contact-edite-form')
        if (contactForm) {
            const fields = {
                PHONE: [],
                EMAIL: []
            }
            if (contactForm.hasAttribute('data-cid')) {
                fields['ID'] = contactForm.getAttribute('data-cid')
            }
            const inputs = ontactForm.querySelectorAll('input')
            for (const input of inputs) {
                if (input.hasAttribute('data-field')) {
                    const field = input.getAttribute('data-field')
                    if (field === 'PHONE') {
                        fields.PHONE.push(input.value.trim())
                    } else if (field === 'EMAIL') {
                        fields.EMAIL.push(input.value.trim())
                    } else {
                        fields[field] = input.value
                    }
                }
            }
            fetch(location.origin + '/local/components/dev/company.popup/ajax.php', {
                method: 'POST',
                body: JSON.stringify(fields)
            })
                .then(console.log)
                .catch(console.error)
        }
    }


//---------------------------------- buttons events listeners ----------------------------------
    const buttonsContainer = document.querySelector('.ui-form-buttons.contact-form-buttons')
    if (buttonsContainer) {
        const saveButton = buttonsContainer.querySelector('.save-button')
        const cancelButton = buttonsContainer.querySelector('.cancel-button')

        if (saveButton) {
            saveButton.addEventListener('click', () => {
                console.log('click save')
                handleSaveClick()
            })
        }

        if (cancelButton) {
            cancelButton.addEventListener('click', () => {
                console.log('click cancel')
            })
        }
    }


})
