import { mergeDeep } from "../helper/helpers";

export default class Translator {
    static dictionary = {};

    constructor(app) {
        mergeDeep(app, this);
    }

    static async initialize(app) {
        if (!Object.keys(Translator.dictionary).length) {
            const results = await app.api.getLocalization();
            if (results.success) {
                Translator.dictionary = results.dictionary;
            }
            return true;
        }

        return false;
    }

    trans = (key) => {
        return Translator.dictionary?.[key] || key;
    };
}
