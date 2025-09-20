<?php
/**
 * Registration editor toolbar behaviour tests.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);

namespace FBM\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/**
 * @coversNothing
 */
final class RegistrationEditorToolbarScriptTest extends TestCase {
        private const TOOLBAR_SNIPPET = '[text* fbm_first_name placeholder "Enter your first name" autocomplete "given-name"]';

        public function test_toolbar_inserts_snippet_with_codemirror(): void {
                $result = $this->runToolbarScript('codemirror');

                $this->assertTrue($result['prevented'], 'Click should be prevented.');
                $this->assertTrue($result['fbmEditor'], 'CodeMirror handle should be registered globally.');
                $this->assertSame(array(self::TOOLBAR_SNIPPET), $result['replacements']);
                $this->assertTrue($result['editorFocused'], 'CodeMirror should be focused before inserting.');
        }

        public function test_toolbar_inserts_snippet_with_textarea_fallback(): void {
                $result = $this->runToolbarScript('textarea');

                $this->assertTrue($result['prevented'], 'Click should be prevented.');
                $this->assertFalse($result['fbmEditor'], 'Global editor handle should be null when CodeMirror is unavailable.');

                $expectedValue = 'H' . self::TOOLBAR_SNIPPET . 'lo';
                $this->assertSame($expectedValue, $result['value']);

                $expectedCaret = 1 + strlen(self::TOOLBAR_SNIPPET);
                $this->assertSame($expectedCaret, $result['selectionStart']);
                $this->assertSame($expectedCaret, $result['selectionEnd']);
                $this->assertTrue($result['focused'], 'Textarea should regain focus after insertion.');
        }

        /**
         * Execute the toolbar script test harness inside Node.js.
         *
         * @param string $scenario Scenario identifier.
         *
         * @return array<string,mixed>
         */
        private function runToolbarScript(string $scenario): array {
                $snippet = self::TOOLBAR_SNIPPET;
                $jsonSnippet = json_encode($snippet, JSON_THROW_ON_ERROR);

                $scriptTemplate = <<<'JS'
const fs = require('fs');
const vm = require('vm');
const path = require('path');

const scenario = process.argv[2] || 'codemirror';
const snippet = %s;

function noop() {}

const state = { handlers: {} };

function registerHandler(event, selector, handler) {
        state.handlers[`${event}:${selector}`] = handler;
}

const rootForm = {
        className: 'fbm-registration-editor__form',
        addEventListener: noop,
        requestSubmit: () => { state.requestSubmit = true; },
        submit: () => { state.submit = true; },
        querySelector: () => null,
};

const textarea = {
        value: 'Hello',
        selectionStart: 1,
        selectionEnd: 3,
        focused: false,
        focus() { this.focused = true; },
        addEventListener: noop,
        setSelectionRange(start, end) {
                this.selectionStart = start;
                this.selectionEnd = end;
        },
};

const previewButton = { addEventListener: noop, focus: noop, setAttribute: noop };
const debugToggle = { setAttribute: noop, textContent: '', addEventListener: noop, focus: noop };
const modal = { addEventListener: noop, setAttribute: noop, removeAttribute: noop, querySelector: () => null, focus: noop, classList: { add: noop, remove: noop, contains: () => false } };
const announcer = { textContent: '' };
const snippetButton = { dataset: { fbmSnippet: snippet }, focus: () => { state.buttonFocused = true; } };

const documentStub = {
        querySelector(selector) {
                switch (selector) {
                        case '.fbm-registration-editor__form':
                                return rootForm;
                        case '#fbm_registration_template':
                                return textarea;
                        case '.fbm-registration-editor__preview':
                                return previewButton;
                        case '.fbm-registration-editor__toolbar .fbm-registration-editor__snippet':
                                return snippetButton;
                        case '[data-fbm-announcer]':
                                return announcer;
                        case '[data-fbm-preview-modal]':
                                return modal;
                        case '[data-fbm-debug-toggle]':
                                return debugToggle;
                        default:
                                return null;
                }
        },
        querySelectorAll() { return []; },
        addEventListener: noop,
        getElementById(id) { return id === 'fbm_registration_template' ? textarea : null; },
        createElement() { return { setAttribute: noop, appendChild: noop, remove: noop, style: {}, classList: { add: noop, remove: noop } }; },
        body: { appendChild: noop, classList: { add: noop, remove: noop } },
};

global.Node = function Node() {};

global.window = {
        fbmRegistrationEditor: {
                textareaId: 'fbm_registration_template',
                codeEditor: scenario === 'codemirror' ? { codemirror: null } : {},
                i18n: {},
                autosave: {},
        },
        fbmRegistrationEditorCodeEditor: scenario === 'codemirror' ? { codemirror: null } : {},
        fbmRegistrationConditions: null,
        requestAnimationFrame: (cb) => { if (typeof cb === 'function') { cb(); } return 0; },
        cancelAnimationFrame: noop,
        requestIdleCallback: undefined,
        cancelIdleCallback: undefined,
        addEventListener: noop,
        removeEventListener: noop,
        fetch: () => Promise.resolve({ ok: true, json: () => Promise.resolve({ markup: '<div></div>' }) }),
        alert: noop,
        setTimeout: (fn) => { if (typeof fn === 'function') { fn(); } return 0; },
        clearTimeout: noop,
        setInterval: () => 0,
        clearInterval: noop,
        matchMedia: () => ({ matches: false, addEventListener: noop, removeEventListener: noop }),
        navigator: { clipboard: { writeText: () => Promise.resolve() } },
        document: documentStub,
        FBM_REG_EDITOR: null,
        performance: { now: () => 0 },
};

global.document = documentStub;

global.jQuery = function (target) {
        if (target === documentStub) {
                return {
                        on(event, selector, handler) {
                                registerHandler(event, selector, handler);
                        },
                        data: () => null,
                };
        }

        return {
                data(key) {
                        if (key === 'fbm-snippet') {
                                return target && target.dataset ? target.dataset.fbmSnippet : null;
                        }
                        return null;
                },
                on: noop,
        };
};

global.jQuery.ajax = () => Promise.resolve();
global.jQuery.fn = { extend: noop };
global.jQuery.event = { add: noop };

const filePath = path.resolve(process.cwd(), '../assets/js/registration-editor.js');
const code = fs.readFileSync(filePath, 'utf8');

const docApi = {
        replacements: [],
        replaceSelection(text) {
                this.replacements.push(text);
        },
};

const cm = {
        getDoc() {
                return docApi;
        },
        focus() {
                state.editorFocused = true;
        },
};

if (scenario === 'codemirror') {
        window.fbmRegistrationEditor.codeEditor.codemirror = cm;
        window.fbmRegistrationEditorCodeEditor.codemirror = cm;
}

if (scenario === 'textarea') {
        window.fbmRegistrationEditorCodeEditor = {};
        window.fbmRegistrationEditor.codeEditor = {};
}

try {
        vm.runInThisContext(code, { filename: 'registration-editor.js' });
} catch (error) {
        console.error('bootstrap_error', error);
        process.exit(1);
}

const handler = state.handlers['click:.fbm-registration-editor__snippet'];
if (!handler) {
        console.error('no_handler');
        process.exit(1);
}

const event = {
        preventDefault() {
                state.prevented = true;
        },
};

handler.call(snippetButton, event);

const output = {
        prevented: !!state.prevented,
        fbmEditor: !!window.FBM_REG_EDITOR,
};

if (scenario === 'codemirror') {
        output.replacements = docApi.replacements;
        output.editorFocused = !!state.editorFocused;
} else {
        output.value = textarea.value;
        output.selectionStart = textarea.selectionStart;
        output.selectionEnd = textarea.selectionEnd;
        output.focused = textarea.focused;
}

process.stdout.write(JSON.stringify(output));
JS;

                $script = sprintf($scriptTemplate, $jsonSnippet);

                $tmp = tempnam(sys_get_temp_dir(), 'fbm-toolbar');
                if (false === $tmp) {
                        $this->fail('Unable to create temporary file.');
                }

                $scriptFile = $tmp . '.js';
                file_put_contents($scriptFile, $script);

                $process = new Process(['node', $scriptFile, $scenario], dirname(__DIR__, 1));
                $process->mustRun();

                $output = $process->getOutput();
                $result = json_decode($output, true);

                @unlink($scriptFile);
                @unlink($tmp);

                $this->assertIsArray($result, 'Node output should decode to array.');

                return $result;
        }
}
