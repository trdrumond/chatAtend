/**
 * Firefox: TinyMCE 5.x pode acessar MouseEvent.mozInputSource (API obsoleta).
 * Carregar antes do tinymce.min.js. O patch principal está na blacklist do
 * bundle (mozInputSource); este shim evita leituras residuais no protótipo.
 */
(function (window) {
    'use strict';

    if (typeof MouseEvent === 'undefined') {
        return;
    }

    function mapPointerTypeToMozSource(pointerType) {
        if (pointerType === 'pen') {
            return 2;
        }
        if (pointerType === 'touch') {
            return 3;
        }
        return 1;
    }

    function mozInputSourceShim() {
        if (typeof PointerEvent !== 'undefined' && this instanceof PointerEvent && this.pointerType) {
            return mapPointerTypeToMozSource(this.pointerType);
        }
        return 1;
    }

    var proto = MouseEvent.prototype;
    var desc = Object.getOwnPropertyDescriptor(proto, 'mozInputSource');

    try {
        if (desc && desc.configurable) {
            delete proto.mozInputSource;
        }
        Object.defineProperty(proto, 'mozInputSource', {
            configurable: true,
            enumerable: false,
            get: mozInputSourceShim
        });
    } catch (err) {
        /* ignore */
    }
}(window));
