export default class LocalStorage {
    generateKey(key) {
        return `expand_${key}`;
    }

    set(key, value) {
        localStorage.setItem(this.generateKey(key), value);
    }

    get(key) {
        return localStorage.getItem(this.generateKey(key));
    }

    clear() {
        localStorage.clear();
    }

    remove(key) {
        localStorage.removeItem(this.generateKey(key));
    }
}
