class MagicLinkAuth {
    constructor(formId, options = {}) {
        this.form = document.getElementById(formId);
        this.ajaxUrl = options.ajaxUrl || ''; 
        this.enableLogging = options.enableLogging || false; // Get enableLogging
        if (this.form) {
            this.form.addEventListener('submit', this.handleSubmit.bind(this));
        }
    }

    async handleSubmit(event) {
        event.preventDefault();
        const email = this.form.querySelector('[name="email"]').value;
        const returnUrl = this.form.querySelector('[name="returnUrl"]').value;
        const security = this.form.querySelector('[name="security"]').value;
        const sessionId = this.form.querySelector('[name="sessionId"]').value;

        try {
            const response = await fetch(this.ajaxUrl, { 
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=send_magic_link&email=${email}&returnUrl=${returnUrl}&security=${security}&sessionId=${sessionId}`,
            });

            const data = await response.json();

            if (data.success) {
                if (this.enableLogging)
                    console.log('Magic link sent successfully:', data);
                
                window.postMessage({ type: 'wpMagicLinkAuthSuccess', data: data }, '*');
            } else {
                if (this.enableLogging)
                    console.error('Error sending magic link:', data);
                
                window.postMessage({ type: 'wpMagicLinkAuthError', data: data }, '*');
            }
        } catch (error) {
            console.error('An error occurred:', error);
            window.postMessage({ type: 'wpMagicLinkAuthError', data: { message: 'An error occurred. Please try again later.' } }, '*');
        }
    }
}