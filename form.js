function signupForm() {
    return {
        form: {
            name: '',
            surname: '',
            nationality: '',
            countryCode: '',
            phone: '',
            email: '',
            password: ''
        },
        countryCodes: {
            "United States": "+1", "United Kingdom": "+44", "Canada": "+1",
            "Germany": "+49", "France": "+33", "Italy": "+39", "Spain": "+34",
            "Netherlands": "+31", "Belgium": "+32", "Switzerland": "+41",
            "Austria": "+43", "Sweden": "+46", "Norway": "+47", "Denmark": "+45",
            "Finland": "+358", "Poland": "+48", "Japan": "+81", "India": "+91",
            "Nigeria": "+234", "Australia": "+61", "China": "+86", "South Africa": "+27"
        },
        init() {
            this.$watch('form.nationality', (value) => {
                this.form.countryCode = this.countryCodes[value] || '';
            });
        }
    }
}
