const vindec = require('vindec');
export default {
    validate(vin) {
        return this.vinLetters(vin) && vin.length == 17
    },
    validateShort(vin) {
        return this.vinLetters(vin) && (vin.length == 7 || vin.length == 8)
    },
    vinLetters(vin) {
        return vin.trim().match(/^[0-9a-zA-Z]+$/)
    }
}