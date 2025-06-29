function signupForm() {
    return {
        form: {
            name: '',
            surname: '',
            nationality: '',
            countryCode: '',
            phone: '',
            email: '',
            password: '',
            agreed_terms: false
        },
        errors: {
            name: '',
            surname: '',
            nationality: '',
            phone: '',
            email: '',
            password: '',
            agreed_terms: ''
        },
        showTermsModal: false,
        isSubmitting: false,
        countryCodes: {
            "United States": "+1",
            "United Kingdom": "+44",
            "Canada": "+1",
            "Germany": "+49",
            "France": "+33",
            "Italy": "+39",
            "Spain": "+34",
            "Netherlands": "+31",
            "Belgium": "+32",
            "Switzerland": "+41",
            "Austria": "+43",
            "Sweden": "+46",
            "Norway": "+47",
            "Denmark": "+45",
            "Finland": "+358",
            "Poland": "+48",
            "Czech Republic": "+420",
            "Hungary": "+36",
            "Romania": "+40",
            "Bulgaria": "+359",
            "Greece": "+30",
            "Portugal": "+351",
            "Ireland": "+353",
            "Australia": "+61",
            "New Zealand": "+64",
            "Japan": "+81",
            "South Korea": "+82",
            "China": "+86",
            "India": "+91",
            "Brazil": "+55",
            "Mexico": "+52",
            "Argentina": "+54",
            "Chile": "+56",
            "Colombia": "+57",
            "Peru": "+51",
            "Venezuela": "+58",
            "South Africa": "+27",
            "Egypt": "+20",
            "Nigeria": "+234",
            "Kenya": "+254",
            "Morocco": "+212",
            "Tunisia": "+216",
            "Algeria": "+213",
            "Libya": "+218",
            "Sudan": "+249",
            "Ethiopia": "+251",
            "Uganda": "+256",
            "Tanzania": "+255",
            "Ghana": "+233",
            "Senegal": "+221",
            "Ivory Coast": "+225",
            "Cameroon": "+237"
        },

        init() {
            // Clear errors on input
            this.$watch('form.name', () => { this.errors.name = ''; });
            this.$watch('form.surname', () => { this.errors.surname = ''; });
            this.$watch('form.nationality', () => { this.errors.nationality = ''; });
            this.$watch('form.phone', () => { this.errors.phone = ''; });
            this.$watch('form.email', () => { this.errors.email = ''; });
            this.$watch('form.agreed_terms', () => { this.errors.agreed_terms = ''; });
        },

        updateCountryCode() {
            this.form.countryCode = this.countryCodes[this.form.nationality] || '';
        },

        validatePassword() {
            const password = this.form.password;
            this.errors.password = '';
            
            if (password.length === 0) return;
            
            if (password.length < 5) {
                this.errors.password = 'Password must be at least 5 characters long';
                return;
            }
            
            const numberCount = (password.match(/\d/g) || []).length;
            if (numberCount < 2) {
                this.errors.password = 'Password must contain at least 2 numbers';
                return;
            }
        },

        async checkEmailExists() {
            if (!this.form.email || !this.validateEmail(this.form.email)) return;
            
            try {
                const response = await fetch(`signup2.php?check_email=${encodeURIComponent(this.form.email)}`);
                const data = await response.json();
                
                if (data.exists) {
                    this.errors.email = 'Email already exists. Please use a different email.';
                }
            } catch (error) {
                console.error('Error checking email:', error);
            }
        },

        validateEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        },

        validatePhone(phone) {
            // Allow digits, spaces, hyphens, plus, parentheses
            return /^[\d\+\-\s\(\)]+$/.test(phone) && phone.replace(/[\D]/g, '').length >= 7;
        },

        validateForm(event) {
            let hasErrors = false;
            
            // Reset all errors
            Object.keys(this.errors).forEach(key => {
                this.errors[key] = '';
            });

            // Validate all fields
            if (!this.form.name.trim()) {
                this.errors.name = 'First name is required';
                hasErrors = true;
            }

            if (!this.form.surname.trim()) {
                this.errors.surname = 'Last name is required';
                hasErrors = true;
            }

            if (!this.form.nationality) {
                this.errors.nationality = 'Nationality is required';
                hasErrors = true;
            }

            if (!this.form.phone.trim()) {
                this.errors.phone = 'Phone number is required';
                hasErrors = true;
            } else if (!this.validatePhone(this.form.phone)) {
                this.errors.phone = 'Please enter a valid phone number';
                hasErrors = true;
            }

            if (!this.form.email.trim()) {
                this.errors.email = 'Email is required';
                hasErrors = true;
            } else if (!this.validateEmail(this.form.email)) {
                this.errors.email = 'Please enter a valid email address';
                hasErrors = true;
            }

            if (!this.form.password) {
                this.errors.password = 'Password is required';
                hasErrors = true;
            } else {
                this.validatePassword();
                if (this.errors.password) hasErrors = true;
            }

            if (!this.form.agreed_terms) {
                this.errors.agreed_terms = 'You must agree to the terms and conditions';
                hasErrors = true;
            }

            if (hasErrors) {
                event.preventDefault();
                return false;
            }

            this.isSubmitting = true;
            return true;
        }
    }
}

function loginForm() {
    return {
        form: {
            email: '',
            password: ''
        },
        errors: {
            email: '',
            password: ''
        },
        isSubmitting: false,

        init() {
            // Clear errors on input
            this.$watch('form.email', () => { this.errors.email = ''; });
            this.$watch('form.password', () => { this.errors.password = ''; });
        },

        validateEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        },

        validateForm(event) {
            let hasErrors = false;
            
            // Reset errors
            this.errors.email = '';
            this.errors.password = '';

            if (!this.form.email.trim()) {
                this.errors.email = 'Email is required';
                hasErrors = true;
            } else if (!this.validateEmail(this.form.email)) {
                this.errors.email = 'Please enter a valid email address';
                hasErrors = true;
            }

            if (!this.form.password) {
                this.errors.password = 'Password is required';
                hasErrors = true;
            }

            if (hasErrors) {
                event.preventDefault();
                return false;
            }

            this.isSubmitting = true;
            return true;
        }
    }
}

function welcomePage() {
    return {
        showMenu: false,
        
        toggleMenu() {
            this.showMenu = !this.showMenu;
        },
        
        closeMenu() {
            this.showMenu = false;
        }
    }
}

function profilePage() {
    return {
        showMenu: false,
        user: {},
        loading: true,
        
        toggleMenu() {
            this.showMenu = !this.showMenu;
        },
        
        closeMenu() {
            this.showMenu = false;
        },

        async loadUserData() {
            try {
                const response = await fetch('profile.php?get_user_data=1');
                const data = await response.json();
                this.user = data;
                this.loading = false;
            } catch (error) {
                console.error('Error loading user data:', error);
                this.loading = false;
            }
        },

        init() {
            this.loadUserData();
        }
    }
}
