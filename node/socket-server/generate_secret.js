const crypto = require('crypto');
const len = process.argv[2] ? parseInt(process.argv[2], 10) : 32;
console.log(crypto.randomBytes(len).toString('hex'));
module.exports = () => crypto.randomBytes(len).toString('hex');