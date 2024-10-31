//eCode v0.1.12b
/*Symbols types:
    0 - digits (48 - 57),
    1 - latin lower (97 - 122),
    2 - latin upper (65 - 90),
    3 - cyr lower (1072 - 1103) unicode,
    4 - cyr upper (1040 - 1071) unicode
    5 - signs (array)*/
function Builder() {
    this.Enhanced = false
    this.useCyrillic = false;
    this.useSigns = false;
    this.digitPercent = 0.3;
    this.signPercent = 0.0;
    this.useShuffle = false;
    this.shuffleRounds = 3;
    this.noRepeat = false;
    this.length = 8;
    var repeatTable;

    Builder.prototype.Result = function Result() {
        var count = this.length;
        var symbolType = 0;
        var result = "";
        var char = 0;
        var chars = new Array();
        var digitCount = Math.round(this.digitPercent * this.length);
        var signCount = Math.round(this.signPercent * this.length);
        var letterCount = this.length - digitCount - signCount;
        var signs = [33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47, 58, 59, 60, 61, 62, 63, 64, 91, 92, 93, 94, 95, 96, 123, 124, 125, 126];
        var signsCount = signs.length;
        var i = 0;
        var iDigit = 0;
        var iLetter = 0;
        var iSign = 0;
        repeatTable = new Array();
        if (this.noRepeat) {
            if (digitCount > 10) throw new Error("Количество цифр не должно превышать 10");
            if (signCount > signsCount) throw new Error("Количество спецсимволов не должно превышать " + signsCount);
            if (!this.useCyrillic) {
                if (letterCount > 52) throw new Error("Количество букв не должно превышать 52");
            }
        }
        while (i < count) {
            if (this.useCyrillic && this.useSigns) {
                symbolType = Math.round(Math.random() * 5);
            } else if (this.useCyrillic && (!this.useSigns)) {
                symbolType = Math.round(Math.random() * 4);
            } else if ((!this.useCyrillic) && (!this.useSigns)) {
                symbolType = Math.round(Math.random() * 2);
            } else if ((!this.useCyrillic) && this.useSigns) {
                symbolType = Math.round(Math.random() * 5);
                if (symbolType == 3) symbolType = 1;
                if (symbolType == 4) symbolType = 2;
            }
            switch (symbolType) {
                case 0:
                    if (iDigit == digitCount && this.Enhanced) break;
                    char = 57 - Math.round(Math.random() * 9);
                    if (this.noRepeat /*&& this.Enhanced*/) {
                        if (IsRepeat(char)) {
                            char = 0;
                            break;
                        }
                    }
                    repeatTable.push(char);
                    chars.push(String.fromCharCode(char));
                    iDigit++;
                    break;
                case 1:
                    if (iLetter == letterCount && this.Enhanced) break;
                    char = 122 - Math.round(Math.random() * 25);
                    if (this.noRepeat /*&& this.Enhanced*/) {
                        if (IsRepeat(char)) {
                            char = 0;
                            break;
                        }
                    }
                    repeatTable.push(char);
                    chars.push(String.fromCharCode(char));
                    iLetter++;
                    break;
                case 2:
                    if (iLetter == letterCount && this.Enhanced) break;
                    char = 90 - Math.round(Math.random() * 25);
                    if (this.noRepeat /*&& this.Enhanced*/) {
                        if (IsRepeat(char)) {
                            char = 0;
                            break;
                        }
                    }
                    repeatTable.push(char);
                    chars.push(String.fromCharCode(char));
                    iLetter++;
                    break;
                case 3:
                    if (iLetter == letterCount && this.Enhanced) break;
                    char = 1071 - Math.round(Math.random() * 31);
                    if (this.noRepeat /*&& this.Enhanced*/) {
                        if (IsRepeat(char)) {
                            char = 0;
                            break;
                        }
                    }
                    repeatTable.push(char);
                    chars.push(String.fromCharCode(char));
                    iLetter++;
                    break;
                case 4:
                    if (iLetter == letterCount && this.Enhanced) break;
                    char = 1103 - Math.round(Math.random() * 31);
                    if (this.noRepeat /*&& this.Enhanced*/) {
                        if (IsRepeat(char)) {
                            char = 0;
                            break;
                        }
                    }
                    repeatTable.push(char);
                    chars.push(String.fromCharCode(char));
                    iLetter++;
                    break;
                case 5:
                    if (iSign == signCount && this.Enhanced) break;
                    char = signs[Math.round(Math.random() * (signsCount - 1))];
                    if (this.noRepeat /*&& this.Enhanced*/) {
                        if (IsRepeat(char)) {
                            char = 0;
                            break;
                        }
                    }
                    repeatTable.push(char);
                    chars.push(String.fromCharCode(char));
                    iSign++;
                    break;
            }
            if (char != 0) {
                i++;
                char = 0;
            }
        }
        if (this.useShuffle && this.Enhanced) {
            for (var i = 0; i < this.shuffleRounds; i++) {
                chars = this.Shuffle(chars);
            }
        }
        result = chars.join("");
        return result;
    }

    Builder.prototype.Shuffle = function Shuffle(value) {
        var result = value;
        var temp;
        var iRandom;
        var count = result.length;
        for (var i = 0; i < count; i++) {
            temp = result[i];
            iRandom = Math.round(Math.random() * count);
            result[i] = result[iRandom];
            result[iRandom] = temp;
        }
        return result;
    }

    function IsRepeat(value) {
        var count = repeatTable.length;
        var nonEmpty = (count != 0);
        if (nonEmpty) {
            for (var i = 0; i < count; i++) {
                if (value == repeatTable[i]) return true;
            }
        }
        return false;
    }
}

function Entropy() {
    Math.logb = function (x) {
        return Math.log(x) / Math.log(2);
    }
    this.Minimum = Math.logb(10);
    this.Maximum = 256;
    this.Password = "";
    this.Variants = 0;
    this.BruteForceTime = 0;

    Entropy.prototype.Current = function Current() {
        var length = this.Password.length
        var alphabel = 0;
        var haveDigit = this.Password.match(/\d/);
        var haveLowLatinLetter = this.Password.match(/[a-z]/);
        var haveLowCyrillicLetter = this.Password.match(/[а-я]/);
        var haveUpLatinLetter = this.Password.match(/[A-Z]/);
        var haveUpCyrillicLetter = this.Password.match(/[А-Я]/);
        var haveSign = this.Password.match(/[\x21-\x2F]|[\x3A-\x40]|[\x5B-\x60]|[\x7B-\x7E]/);
        if (haveDigit) {
            alphabel += 10;
        }
        if (haveLowLatinLetter) {
            alphabel += 26;
        }
        if (haveLowCyrillicLetter) {
            alphabel += 32;
        }
        if (haveUpLatinLetter) {
            alphabel += 26;
        }
        if (haveUpCyrillicLetter) {
            alphabel += 32;
        }
        if (haveSign) {
            alphabel += 32;
        }
        var difference = calculateDifference(this.Password);
        this.Variants = Math.pow(alphabel, length);
        this.BruteForceTime = this.Variants / 100000000;
        return Math.logb(this.Variants) - difference;
    }

    function calculateDifference(value) {
        var returnValue = 0;
        var length = value.length;
        var symbolCount, symbolType, symbolExcess;
        var equal = new Array;
        equal[0] = 0;
        equal[1] = 0;
        equal[2] = 0;
        equal[3] = 0;
        equal[4] = 0;
        equal[5] = 0;
        for (var i = 0; i < length; i++) {
            symbolType = haveSymbolType(value.charAt(i));
            symbolCount = 0;
            var position = value.indexOf(value.charAt(i))
            while (position != -1) {
                symbolCount++;
                position = value.indexOf(value.charAt(i), position + 1);
            }
            symbolExcess = symbolCount - 1;
            if (symbolExcess != 0) {
                equal[symbolType] = symbolExcess;
            }
        }
        returnValue += equal[0] * Math.logb(10);
        returnValue += equal[1] * Math.logb(26);
        returnValue += equal[2] * Math.logb(26);
        returnValue += equal[3] * Math.logb(32);
        returnValue += equal[4] * Math.logb(32);
        returnValue += equal[5] * Math.logb(32);
        return returnValue;
    }

    function haveSymbolType(value) {
        var returnValue;
        var haveDigit = value.match(/\d/);
        var haveLowLatinLetter = value.match(/[a-z]/);
        var haveLowCyrillicLetter = value.match(/[а-я]/);
        var haveUpLatinLetter = value.match(/[A-Z]/);
        var haveUpCyrillicLetter = value.match(/[А-Я]/);
        var haveSign = value.match(/[\x21-\x2F]|[\x3A-\x40]|[\x5B-\x60]|[\x7B-\x7E]/);
        if (haveDigit) {
            returnValue = 0;
        }
        if (haveLowLatinLetter) {
            returnValue = 1;
        }
        if (haveLowCyrillicLetter) {
            returnValue = 3;
        }
        if (haveUpLatinLetter) {
            returnValue = 2;
        }
        if (haveUpCyrillicLetter) {
            returnValue = 4;
        }
        if (haveSign) {
            returnValue = 5;
        }
        return returnValue;
    }
}
