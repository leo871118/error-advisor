<?php

declare(strict_types=1);

namespace LeandroPerez\ErrorAdvisor\Catalog;

use LeandroPerez\ErrorAdvisor\Dto\ErrorRule;

/**
 * Diagnostic rules for Python exceptions, imports, types, syntax, runtime, and standard libraries.
 */
final class PythonErrorCatalog
{
    /**
     * @return list<ErrorRule>
     */
    public static function all(): array
    {
        return [
            new ErrorRule(
                id: 'py.module_not_found',
                category: 'Python Environment & Imports',
                title: 'ModuleNotFoundError: No Module Named "{match}"',
                pattern: '/ModuleNotFoundError:\s*No module named\s*["\']([^"\'\r\n]+)["\']/i',
                explanation: 'Python cannot locate module `{match}` in `sys.path`. The package is either not installed in the active virtual environment or the virtualenv is not activated.',
                solutions: [
                    'Install the missing module using `pip install {match}` within your active virtual environment.',
                    'Verify your active environment with `which python` / `where python` and ensure your IDE/terminal points to the correct virtualenv.',
                    'Check `PYTHONPATH` or add project root to `sys.path` for local multi-package modules.',
                ],
                codeExample: "pip install {match}\n# Or ensure virtualenv is activated:\nsource .venv/bin/activate  # Linux/macOS\n.venv\\Scripts\\activate      # Windows",
                tags: ['python', 'pip', 'modules', 'imports'],
                confidence: 0.99,
                titleIt: 'ModuleNotFoundError: Modulo "{match}" Non Trovato',
                explanationIt: 'Python non trova il modulo `{match}` nel `sys.path`. Il pacchetto non è installato nel virtual environment corrente oppure l\'ambiente virtuale non è attivo.',
                solutionsIt: [
                    'Installa il pacchetto mancante tramite `pip install {match}` nel virtualenv attivo.',
                    'Verifica l\'interprete in uso con `which python` / `where python` e controlla che l\'IDE punti al virtual environment corretto.',
                    'Verifica la variabile d\'ambiente `PYTHONPATH` se si tratta di moduli locali interni al progetto.',
                ],
            ),
            new ErrorRule(
                id: 'py.import_error_name',
                category: 'Python Environment & Imports',
                title: 'ImportError: Cannot Import Name "{match}"',
                pattern: '/ImportError:\s*cannot import name\s*["\']([^"\'\r\n]+)["\']/i',
                explanation: 'Python cannot import symbol `{match}` from the target module, often caused by circular imports, version deprecations, or shadowing the module name with a local file.',
                solutions: [
                    'Check for circular dependencies where module A imports module B which also imports module A.',
                    'Ensure a local script does not have the same name as a standard or third-party library (e.g. `email.py`, `requests.py`).',
                    'Verify package version compatibility as functions and classes are sometimes renamed or moved across versions.',
                ],
                codeExample: "# Refactor to avoid circular imports, e.g. import inside function scope\ndef run():\n    from my_module import {match}",
                tags: ['python', 'imports', 'circular-import'],
                confidence: 0.98,
                titleIt: 'ImportError: Impossibile Importare il Nome "{match}"',
                explanationIt: 'Python non riesce a importare il simbolo `{match}` dal modulo specificato, frequentemente per dipendenze circolari o conflitti di omonimia tra file locali e librerie.',
                solutionsIt: [
                    'Controlla la presenza di import circolari in cui due moduli si importano a vicenda.',
                    'Assicurati che nessun file locale abbia il nome di un pacchetto noto (es. non chiamare script `email.py`, `random.py`, `requests.py`).',
                    'Verifica la versione della libreria: simboli o funzioni potrebbero essere stati rinominati o spostati.',
                ],
            ),
            new ErrorRule(
                id: 'py.indentation_error',
                category: 'Python Syntax',
                title: 'IndentationError: Inconsistent or Unexpected Indentation',
                pattern: '/IndentationError:\s*(?:unexpected indent|expected an indented block|unindent does not match)/i',
                explanation: 'Python uses indentation to define code blocks. This error occurs when mixing tabs and spaces, forgetting an indent after `:` or misaligning blocks.',
                solutions: [
                    'Configure your editor to convert tabs to 4 spaces consistently (PEP 8 standard).',
                    'Add `pass` or an implementation if a function, class, `if`, or `for` block was left empty after `:`.',
                    'Run a code formatter such as `black` or `ruff format` to auto-fix indentation.',
                ],
                codeExample: "ruff format myfile.py\n# or\nblack myfile.py",
                tags: ['python', 'syntax', 'indentation'],
                confidence: 0.99,
                titleIt: 'IndentationError: Rientro Non Coerente o Inatteso',
                explanationIt: 'Python utilizza l\'indentazione per delimitare i blocchi di codice. L\'errore indica una combinazione disomogenea di spazi e tabulazioni o un blocco mancante dopo `:`.',
                solutionsIt: [
                    'Configura il tuo editor per utilizzare 4 spazi per tabulazione in conformità allo standard PEP 8.',
                    'Inserisci `pass` o l\'implementazione se un blocco `def`, `class`, `if` o `for` è rimasto vuoto dopo i due punti `:`.',
                    'Esegui un formatter automatico come `black` o `ruff format` per correggere la struttura dei rientri.',
                ],
            ),
            new ErrorRule(
                id: 'py.syntax_error',
                category: 'Python Syntax',
                title: 'SyntaxError: Invalid Syntax',
                pattern: '/SyntaxError:\s*invalid syntax/i',
                explanation: 'The Python parser encountered invalid syntax, such as a missing colon `:`, unmatched parentheses/brackets, or using reserved keywords.',
                solutions: [
                    'Check the line right above the syntax error caret `^` for unclosed parentheses, quotes, or a missing colon `:` at the end of `def`, `if`, `while`, or `class`.',
                    'Check if using a newer Python syntax feature (e.g. `match`/`case`, walrus `:=`, type union `X | Y`) on an older Python runtime.',
                ],
                codeExample: "python --version  # Verify Python version >= 3.10 for modern syntax",
                tags: ['python', 'syntax'],
                confidence: 0.99,
                titleIt: 'SyntaxError: Sintassi Non Valida',
                explanationIt: 'Il compilatore Python ha rilevato una violazione di sintassi, come due punti `:` mancanti a fine riga, parentesi/virgolette non chiuse o parole riservate.',
                solutionsIt: [
                    'Esamina la riga indicata dal cursore `^` e quella immediatamente precedente per individuare parentesi aperte o `:` mancanti dopo `if`/`def`/`class`.',
                    'Verifica se stai eseguendo codice con sintassi moderna (es. operatore walrus `:=`, `match/case`, union types `X | Y`) su un interprete Python precedente alla 3.10.',
                ],
            ),
            new ErrorRule(
                id: 'py.nonetype_not_subscriptable',
                category: 'Python Types',
                title: 'TypeError: \'NoneType\' Object Is Not Subscriptable',
                pattern: '/TypeError:\s*["\']NoneType["\'] object is not subscriptable/i',
                explanation: 'Square-bracket indexing (`obj[key]` or `obj[index]`) was executed on a variable whose value is `None` (e.g. a failed lookup or a function returning None implicitly).',
                solutions: [
                    'Verify the variable is not `None` before subscripting (`if result is not None: value = result["key"]`).',
                    'Ensure functions return an explicit dictionary or list instead of falling through to return `None`.',
                ],
                codeExample: "data = fetch_record()\nif data is not None:\n    print(data['id'])",
                tags: ['python', 'types', 'none', 'subscript'],
                confidence: 0.98,
                titleIt: 'TypeError: Oggetto \'NoneType\' Non Indicizzabile',
                explanationIt: 'È stato tentato l\'accesso tramite indice o chiave con parentesi quadre (`obj[chiave]`) su un valore che è `None` (es. funzione priva di return o lookup fallito).',
                solutionsIt: [
                    'Controlla che la variabile non sia `None` prima di indicizzarla (`if record is not None: val = record["key"]`).',
                    'Assicurati che la funzione chiamata restituisca esplicitamente una lista o un dizionario e non `None`.',
                ],
            ),
            new ErrorRule(
                id: 'py.nonetype_not_iterable',
                category: 'Python Types',
                title: 'TypeError: \'NoneType\' Object Is Not Iterable',
                pattern: '/TypeError:\s*["\']NoneType["\'] object is not iterable/i',
                explanation: 'A `for` loop or unpack statement attempted to iterate over a variable that contains `None` instead of a list, tuple, or generator.',
                solutions: [
                    'Default to an empty list or iterable with `items = get_items() or []`.',
                    'Check whether a database query, API client call, or regex search returned `None`.',
                ],
                codeExample: "for item in (get_items() or []):\n    process(item)",
                tags: ['python', 'types', 'none', 'iterable'],
                confidence: 0.98,
                titleIt: 'TypeError: Oggetto \'NoneType\' Non Iterabile',
                explanationIt: 'Un ciclo `for` o un\'operazione di unpack tenta di iterare su una variabile che vale `None` anziché su una lista, tupla o generatore.',
                solutionsIt: [
                    'Usa un valore di fallback vuoto (`for elemento in (get_dati() or []):`).',
                    'Verifica se la query o la chiamata API precedente ha restituito `None` e gestisci il caso prima del ciclo.',
                ],
            ),
            new ErrorRule(
                id: 'py.str_concat_type_error',
                category: 'Python Types',
                title: 'TypeError: Can Only Concatenate Str (Not Non-String) to Str',
                pattern: '/TypeError:\s*can only concatenate str \(not ["\']?[a-zA-Z0-9_]+["\']?\) to str/i',
                explanation: 'Python does not implicitly convert integers, floats, or objects to strings when using the `+` concatenation operator.',
                solutions: [
                    'Use formatted string literals (f-strings) instead of `+` concatenation (e.g. `f"Item: {count}"`).',
                    'Cast the non-string operand explicitly with `str(value)`.',
                ],
                codeExample: "message = f\"Order #{order_id} total: \${total}\"",
                tags: ['python', 'types', 'strings', 'f-string'],
                confidence: 0.98,
                titleIt: 'TypeError: Concatenazione Possibile Solo Tra Stringhe',
                explanationIt: 'Python non effettua il cast implicito di numeri o oggetti a stringa durante l\'operazione di concatenazione con l\'operatore `+`.',
                solutionsIt: [
                    'Utilizza le f-string anziché la concatenazione con `+` (es. `f"Conteggio: {count}"`).',
                    'Esegui il cast esplicito con `str(valore)`.',
                ],
            ),
            new ErrorRule(
                id: 'py.missing_positional_argument',
                category: 'Python Functions',
                title: 'TypeError: Missing Required Positional Argument',
                pattern: '/TypeError:.*missing \d+ required positional argument/i',
                explanation: 'A function or method was invoked without supplying all mandatory arguments defined in its parameters list.',
                solutions: [
                    'Check the function or method signature and provide all mandatory arguments.',
                    'If invoking an instance method, make sure `self` is not omitted in the method definition.',
                ],
                codeExample: "class Handler:\n    def process(self, payload):  # Ensure 'self' is the first parameter\n        pass",
                tags: ['python', 'functions', 'arguments'],
                confidence: 0.98,
                titleIt: 'TypeError: Argomento Posizionale Obbligatorio Mancante',
                explanationIt: 'Una funzione o un metodo è stato invocato omettendo uno o più parametri posizionali obbligatori.',
                solutionsIt: [
                    'Verifica la firma della funzione e fornisci tutti gli argomenti richiesti alla chiamata.',
                    'Se si tratta del metodo di una classe, assicurati di non aver dimenticato `self` come primo parametro.',
                ],
            ),
            new ErrorRule(
                id: 'py.positional_argument_count',
                category: 'Python Functions',
                title: 'TypeError: Positional Arguments Count Mismatch',
                pattern: '/TypeError:.*takes \d+ positional arguments? but \d+ (?:were|was) given/i',
                explanation: 'More positional arguments were passed to a function or method than allowed. If calling an instance method, forgetting `self` causes an implicit extra argument.',
                solutions: [
                    'Add `self` as the first argument in class methods (`def my_method(self, arg):`).',
                    'Use `@staticmethod` or `@classmethod` decorators if the method does not require an instance receiver.',
                ],
                codeExample: "class Service:\n    @staticmethod\n    def helper(param):\n        return param",
                tags: ['python', 'functions', 'oop'],
                confidence: 0.98,
                titleIt: 'TypeError: Numero di Argomenti Posizionali Non Corrispondente',
                explanationIt: 'Sono stati passati più argomenti di quanti consentiti dalla funzione. Nei metodi di classe, omettere `self` nella definizione fa sì che Python passi un argomento in più.',
                solutionsIt: [
                    'Aggiungi `self` come primo parametro nei metodi d\'istanza (`def metodo(self, x):`).',
                    'Usa il decoratore `@staticmethod` o `@classmethod` se il metodo non opera sullo stato dell\'istanza.',
                ],
            ),
            new ErrorRule(
                id: 'py.not_json_serializable',
                category: 'Python Libraries',
                title: 'TypeError: Object of Type {match} Is Not JSON Serializable',
                pattern: '/TypeError:\s*Object of type\s+([a-zA-Z0-9_]+)\s+is not JSON serializable/i',
                explanation: 'Standard `json.dumps()` cannot serialize complex Python objects such as `datetime`, `UUID`, `Decimal`, `set`, or custom classes.',
                solutions: [
                    'Convert the object to primitive types before serialization (e.g. `dt.isoformat()` for dates, `str(uuid_obj)` for UUIDs).',
                    'Pass `default=str` to `json.dumps(data, default=str)` or implement a custom JSON encoder.',
                    'Use Pydantic or dataclasses with built-in `.model_dump()` / `.asdict()` methods.',
                ],
                codeExample: "import json\njson_str = json.dumps(payload, default=str)",
                tags: ['python', 'json', 'serialization'],
                confidence: 0.98,
                titleIt: 'TypeError: Oggetto di Tipo {match} Non Serializzabile in JSON',
                explanationIt: 'La funzione `json.dumps()` nativa non è in grado di serializzare tipi complessi quali `datetime`, `UUID`, `Decimal`, `set` o classi personalizzate.',
                solutionsIt: [
                    'Converti l\'oggetto in tipi primitivi prima della serializzazione (es. `dt.isoformat()` per le date, `str(uuid_obj)` per UUID).',
                    'Passa `default=str` a `json.dumps(data, default=str)` oppure usa un encoder JSON personalizzato.',
                    'Sfrutta Pydantic o dataclasses con metodi `.model_dump()` o `dataclasses.asdict()`.',
                ],
            ),
            new ErrorRule(
                id: 'py.attribute_error_none',
                category: 'Python OOP',
                title: 'AttributeError: \'NoneType\' Object Has No Attribute "{match}"',
                pattern: '/AttributeError:\s*["\']NoneType["\'] object has no attribute\s*["\']?([a-zA-Z0-9_]+)["\']?/i',
                explanation: 'Attempted to access attribute or method `.{match}` on a variable that evaluated to `None`.',
                solutions: [
                    'Check whether the preceding method returned `None` due to an unfound item or missing return statement.',
                    'Guard with an explicit condition: `if obj is not None: obj.{match}()`.',
                ],
                codeExample: "match = re.search(r'\\d+', text)\nif match is not None:\n    print(match.group())",
                tags: ['python', 'oop', 'none', 'attributeerror'],
                confidence: 0.99,
                titleIt: 'AttributeError: Oggetto \'NoneType\' Non Ha l\'Attributo "{match}"',
                explanationIt: 'Tentativo di accedere all\'attributo o metodo `.{match}` su un valore nullo (`None`).',
                solutionsIt: [
                    'Verifica se la chiamata precedente (es. ricerca DB, regex `re.search`, find) ha restituito `None`.',
                    'Inserisci un controllo di non-nullità prima dell\'accesso: `if obj is not None: obj.{match}()`.',
                ],
            ),
            new ErrorRule(
                id: 'py.attribute_error_missing',
                category: 'Python OOP',
                title: 'AttributeError: Object Has No Attribute "{match}"',
                pattern: '/AttributeError:\s*["\']([a-zA-Z0-9_]+)["\'] object has no attribute\s*["\']?([a-zA-Z0-9_]+)["\']?/i',
                explanation: 'The target object does not have attribute or method `{match}`. This often indicates a typo, incorrect type, or accessing private fields.',
                solutions: [
                    'Check attribute spelling or use `dir(obj)` to inspect available attributes on the object.',
                    'Check if using `getattr(obj, \'{match}\', default)` for optional dynamic properties.',
                ],
                codeExample: "if hasattr(obj, '{match}'):\n    val = getattr(obj, '{match}')",
                tags: ['python', 'oop', 'attributeerror'],
                confidence: 0.98,
                titleIt: 'AttributeError: L\'Oggetto Non Contiene l\'Attributo "{match}"',
                explanationIt: 'L\'oggetto esaminato non possiede il campo o metodo `{match}`. Spesso dovuto a refusi di battitura o scambio di tipo.',
                solutionsIt: [
                    'Verifica l\'ortografia del nome o usa `dir(oggetto)` per elencare gli attributi disponibili.',
                    'Usa `getattr(oggetto, \'{match}\', default)` per campi opzionali dinamici.',
                ],
            ),
            new ErrorRule(
                id: 'py.key_error',
                category: 'Python Types',
                title: 'KeyError: "{match}"',
                pattern: '/KeyError:\s*["\']?([^"\'\r\n]+)["\']?/i',
                explanation: 'Attempted to access dictionary key `{match}` that does not exist in the dictionary.',
                solutions: [
                    'Use `.get(\'{match}\', default)` instead of direct square-bracket lookup.',
                    'Check key presence with `if \'{match}\' in my_dict:`.',
                ],
                codeExample: "user_id = data.get('{match}', None)",
                tags: ['python', 'dict', 'keyerror'],
                confidence: 0.99,
                titleIt: 'KeyError: Chiave Dizionario Mancante "{match}"',
                explanationIt: 'Tentativo di accedere alla chiave `{match}` non presente nel dizionario.',
                solutionsIt: [
                    'Usa il metodo sicuro `.get(\'{match}\', default)` al posto delle parentesi quadre dirette.',
                    'Controlla l\'esistenza della chiave con `if \'{match}\' in mio_dict:`.',
                ],
            ),
            new ErrorRule(
                id: 'py.index_error_out_of_range',
                category: 'Python Types',
                title: 'IndexError: List Index Out of Range',
                pattern: '/IndexError:\s*(?:list|tuple|sequence) index out of range/i',
                explanation: 'Attempted to access an item at an index greater than or equal to the sequence length, or an empty list.',
                solutions: [
                    'Verify the sequence length with `len(my_list)` before indexing.',
                    'Use slice notation (`my_list[0:1]`) which returns an empty list without raising an IndexError on out-of-range bounds.',
                ],
                codeExample: "first_item = my_list[0] if len(my_list) > 0 else None",
                tags: ['python', 'list', 'indexerror'],
                confidence: 0.99,
                titleIt: 'IndexError: Indice Fuori Scala',
                explanationIt: 'Accesso a un indice superiore o uguale alla lunghezza della sequenza (o lista vuota).',
                solutionsIt: [
                    'Verifica la dimensione della lista con `len(mia_lista)` prima dell\'accesso per indice.',
                    'Utilizza lo slicing (`mia_lista[0:1]`), che restituisce una lista vuota senza sollevare eccezioni se l\'indice non esiste.',
                ],
            ),
            new ErrorRule(
                id: 'py.invalid_literal_int',
                category: 'Python Types',
                title: 'ValueError: Invalid Literal for int() ("{match}")',
                pattern: '/ValueError:\s*invalid literal for int\(\) with base \d+:\s*["\']?([^"\'\r\n]+)["\']?/i',
                explanation: 'Failed to convert string `{match}` to an integer because it contains non-numeric characters, float decimals, or empty whitespace.',
                solutions: [
                    'Strip whitespace with `.strip()` and validate digits with `.isdigit()`.',
                    'If the string represents a floating-point number, parse it with `int(float(value))`.',
                ],
                codeExample: "val = int(float(str_val.strip())) if str_val.strip() else 0",
                tags: ['python', 'valueerror', 'casting', 'int'],
                confidence: 0.98,
                titleIt: 'ValueError: Valore Non Valido per Conversione a int() ("{match}")',
                explanationIt: 'Conversione fallita della stringa `{match}` a intero per presenza di decimali, spazi o caratteri alfabetici.',
                solutionsIt: [
                    'Pulisci la stringa con `.strip()` e verifica i caratteri numerici con `.isdigit()`.',
                    'Se la stringa contiene un numero decimale (es. "12.5"), convertila prima con `int(float(valore))`.',
                ],
            ),
            new ErrorRule(
                id: 'py.unpack_value_error',
                category: 'Python Syntax',
                title: 'ValueError: Values to Unpack Mismatch',
                pattern: '/ValueError:\s*(?:too many values to unpack|not enough values to unpack)/i',
                explanation: 'The number of variables on the left-hand side of an assignment does not match the count of items in the iterable.',
                solutions: [
                    'Use starred expression unpacking (e.g. `first, *middle, last = items`) for variable-length iterables.',
                    'Verify the number of values returned by a function or split operation before unpacking.',
                ],
                codeExample: "key, *rest = line.split(':')",
                tags: ['python', 'unpacking', 'valueerror'],
                confidence: 0.98,
                titleIt: 'ValueError: Discrepanza nel Numero di Elementi da Decomprimere',
                explanationIt: 'Il numero di variabili a sinistra dell\'assegnazione non corrisponde al numero di elementi generati dall\'iterabile.',
                solutionsIt: [
                    'Usa l\'unpacking con asterisco (es. `primo, *resto = elementi`) per gestire sequenze di lunghezza variabile.',
                    'Controlla il numero di elementi restituiti da `.split()` o dalla funzione prima di spacchettarli in variabili.',
                ],
            ),
            new ErrorRule(
                id: 'py.zero_division_error',
                category: 'Python Runtime',
                title: 'ZeroDivisionError: Division or Modulo by Zero',
                pattern: '/ZeroDivisionError:\s*division by zero/i',
                explanation: 'A division (`/`, `//`) or modulo (`%`) operation received zero as the divisor.',
                solutions: [
                    'Guard calculations against zero divisors: `result = (a / b) if b != 0 else 0`.',
                    'Check user input or dataset aggregations for 0 values.',
                ],
                codeExample: "pct = (count / total) * 100 if total > 0 else 0.0",
                tags: ['python', 'math', 'zerodivision'],
                confidence: 0.99,
                titleIt: 'ZeroDivisionError: Divisione per Zero',
                explanationIt: 'Un\'operazione aritmetica di divisione o modulo ha ricevuto zero come divisore.',
                solutionsIt: [
                    'Proteggi l\'operazione con un controllo preventivo: `risultato = (a / b) if b != 0 else 0`.',
                    'Valida i valori dei divisori provenienti da form o query.',
                ],
            ),
            new ErrorRule(
                id: 'py.file_not_found_errno_2',
                category: 'Python Filesystem',
                title: 'FileNotFoundError: [Errno 2] No Such File or Directory',
                pattern: '/FileNotFoundError:.*\[Errno 2\]\s*No such file or directory/i',
                explanation: 'Python could not locate the file or directory at the specified path.',
                solutions: [
                    'Use `pathlib.Path(__file__).resolve().parent` to build absolute paths relative to the current script.',
                    'Verify working directory when launching scripts via CLI (`os.getcwd()`).',
                ],
                codeExample: "from pathlib import Path\nconfig_path = Path(__file__).resolve().parent / 'config.json'",
                tags: ['python', 'filesystem', 'pathlib'],
                confidence: 0.98,
                titleIt: 'FileNotFoundError: [Errno 2] File o Directory Non Trovata',
                explanationIt: 'Python non trova il file o la directory al percorso indicato dal filesystem.',
                solutionsIt: [
                    'Usa `pathlib.Path(__file__).resolve().parent` per calcolare percorsi assoluti affidabili.',
                    'Verifica la working directory corrente da cui viene eseguito lo script (`os.getcwd()`).',
                ],
            ),
            new ErrorRule(
                id: 'py.permission_error_errno_13',
                category: 'Python Filesystem',
                title: 'PermissionError: [Errno 13] Permission Denied',
                pattern: '/PermissionError:.*\[Errno 13\]\s*Permission denied/i',
                explanation: 'The running process lacks read, write, or execute OS permissions for the target file or directory.',
                solutions: [
                    'Check file permissions with `ls -la` (Linux/macOS) or file properties (Windows).',
                    'Ensure the file is not currently locked or opened exclusively by another application.',
                ],
                codeExample: "chmod u+rw path/to/file",
                tags: ['python', 'permissions', 'filesystem'],
                confidence: 0.98,
                titleIt: 'PermissionError: [Errno 13] Permesso Negato',
                explanationIt: 'Il processo non dispone dei permessi operativi di lettura, scrittura o esecuzione sul file o sulla directory.',
                solutionsIt: [
                    'Controlla i permessi con `ls -la` o le proprietà di sicurezza del file su Windows.',
                    'Assicurati che il file non sia bloccato da un altro processo in esecuzione.',
                ],
            ),
            new ErrorRule(
                id: 'py.unicode_decode_error',
                category: 'Python Filesystem',
                title: 'UnicodeDecodeError: \'utf-8\' Codec Can\'t Decode Byte',
                pattern: '/UnicodeDecodeError:\s*["\']utf-8["\'] codec can\'t decode byte/i',
                explanation: 'A file opened as UTF-8 contains characters encoded in a different character set (such as `windows-1252`, `iso-8859-1`, or binary data).',
                solutions: [
                    'Specify the exact encoding when opening the file, e.g. `open(filename, encoding="latin-1")` or `encoding="utf-8-sig"`.',
                    'Use `errors="replace"` or `errors="ignore"` if lossy decoding is acceptable.',
                ],
                codeExample: "with open('file.txt', 'r', encoding='latin-1', errors='replace') as f:\n    content = f.read()",
                tags: ['python', 'encoding', 'unicode'],
                confidence: 0.98,
                titleIt: 'UnicodeDecodeError: Il Codec UTF-8 Non Riesce a Decodificare il Byte',
                explanationIt: 'Un file aperto come UTF-8 contiene byte appartenenti a una codifica differente (es. Windows-1252, ISO-8859-1 o file binario).',
                solutionsIt: [
                    'Specifica l\'encoding corretto nell\'apertura del file: `open(nomefile, encoding="latin-1")` o `encoding="utf-8-sig"`.',
                    'Passa `errors="replace"` o `errors="ignore"` se la perdita di caratteri non standard è tollerabile.',
                ],
            ),
            new ErrorRule(
                id: 'py.unbound_local_error',
                category: 'Python Functions',
                title: 'UnboundLocalError: Local Variable Referenced Before Assignment',
                pattern: '/UnboundLocalError:\s*(?:cannot access local variable\s*["\']?([a-zA-Z0-9_]+)["\']?\s*where it is not associated with a value|local variable\s*["\']?([a-zA-Z0-9_]+)["\']?\s*referenced before assignment)/i',
                explanation: 'A variable was assigned inside a function, causing Python to treat it as a local variable throughout the entire function scope, but it was read prior to assignment.',
                solutions: [
                    'Use the `global` or `nonlocal` keyword if modifying an outer-scope variable.',
                    'Pass the variable as a parameter and return its new value rather than modifying global state.',
                ],
                codeExample: "count = 0\ndef increment():\n    global count\n    count += 1",
                tags: ['python', 'scoping', 'unboundlocalerror'],
                confidence: 0.98,
                titleIt: 'UnboundLocalError: Variabile Locale Referenziata Prima dell\'Assegnazione',
                explanationIt: 'La variabile viene assegnata all\'interno della funzione, rendendola locale all\'intero blocco, ma viene letta prima di tale assegnazione.',
                solutionsIt: [
                    'Dichiara la variabile con `global` o `nonlocal` se intendi modificare una variabile di scope superiore.',
                    'Passa il valore come parametro della funzione e restituisci il nuovo valore evitando variabili globali.',
                ],
            ),
            new ErrorRule(
                id: 'py.recursion_depth_exceeded',
                category: 'Python Runtime',
                title: 'RecursionError: Maximum Recursion Depth Exceeded',
                pattern: '/RecursionError:\s*maximum recursion depth exceeded/i',
                explanation: 'A recursive function reached Python\'s maximum call stack limit (default 1000) due to a missing base case or infinite loop.',
                solutions: [
                    'Verify the recursive base condition to guarantee termination.',
                    'Refactor recursion to an iterative loop with a stack or queue.',
                    'Increase `sys.setrecursionlimit()` only when deep recursion is strictly necessary and verified.',
                ],
                codeExample: "# Convert recursive algorithm to iterative using an explicit loop and list stack",
                tags: ['python', 'recursion', 'stack'],
                confidence: 0.99,
                titleIt: 'RecursionError: Profondità Massima di Ricorsione Superata',
                explanationIt: 'Una funzione ricorsiva ha superato il limite dello stack di chiamate di Python (di norma 1000) a causa dell\'assenza di un caso base o di un ciclo infinito.',
                solutionsIt: [
                    'Verifica la condizione di uscita base della funzione ricorsiva per assicurare la terminazione.',
                    'Trasforma l\'algoritmo ricorsivo in un ciclo iterativo (usando uno stack o una coda esplicita).',
                ],
            ),
            new ErrorRule(
                id: 'py.dict_size_changed_iteration',
                category: 'Python Types',
                title: 'RuntimeError: Dictionary Changed Size During Iteration',
                pattern: '/RuntimeError:\s*dictionary changed size during iteration/i',
                explanation: 'Keys were added or deleted from a dictionary while directly iterating over it.',
                solutions: [
                    'Iterate over a copy of the keys: `for key in list(my_dict.keys()):` or `for k in list(my_dict):`.',
                    'Build a new filtered dictionary using a dictionary comprehension instead of modifying in-place.',
                ],
                codeExample: "for key in list(data.keys()):\n    if should_remove(key):\n        del data[key]",
                tags: ['python', 'dict', 'runtimeerror'],
                confidence: 0.98,
                titleIt: 'RuntimeError: Il Dizionario ha Modificato Dimensione Durante l\'Iterazione',
                explanationIt: 'Sono state aggiunte o rimosse chiavi da un dizionario mentre era in corso l\'iterazione diretta su di esso.',
                solutionsIt: [
                    'Itera su una copia statica delle chiavi: `for chiave in list(mio_dict.keys()):`.',
                    'Crea un nuovo dizionario filtrato tramite dictionary comprehension invece di mutare l\'originale sul posto.',
                ],
            ),
            new ErrorRule(
                id: 'py.requests_connection_or_ssl',
                category: 'Python HTTP & Network',
                title: 'Requests Network / SSL Connection Error',
                pattern: '/requests\.exceptions\.(?:SSLError|ConnectionError)|urllib3\.exceptions\.(?:MaxRetryError|SSLError)/i',
                explanation: 'A Python HTTP request failed due to DNS resolution, connection refusal, or SSL certificate validation issues.',
                solutions: [
                    'Verify the target hostname and internet connectivity.',
                    'Ensure system CA certificates are updated: `pip install certifi` and pass `verify=certifi.where()`.',
                    'Implement retry logic with exponential backoff using `requests.adapters.HTTPAdapter`.',
                ],
                codeExample: "import certifi, requests\nresponse = requests.get('https://api.example.com', verify=certifi.where(), timeout=10)",
                tags: ['python', 'requests', 'ssl', 'network'],
                confidence: 0.98,
                titleIt: 'Requests: Errore di Rete o Connessione SSL',
                explanationIt: 'Una richiesta HTTP con la libreria requests o urllib3 è fallita per problemi di risoluzione DNS, porta chiusa o validazione del certificato SSL.',
                solutionsIt: [
                    'Verifica la correttezza dell\'URL, la raggiungibilità dell\'host e le impostazioni del proxy.',
                    'Aggiorna il bundle certificati CA con `pip install certifi` e passa `verify=certifi.where()`.',
                    'Configura un timeout esplicito e retry automatici con `HTTPAdapter`.',
                ],
            ),
            new ErrorRule(
                id: 'py.json_decode_error',
                category: 'Python Libraries',
                title: 'json.decoder.JSONDecodeError: Invalid JSON Data',
                pattern: '/json\.decoder\.JSONDecodeError:\s*([^\r\n]+)/i',
                explanation: '`json.loads()` failed to parse the input string because it is malformed, truncated, or contains an HTML response (e.g. 500 error page).',
                solutions: [
                    'Inspect the raw string content before decoding; APIs often return HTML error pages during outages.',
                    'Check for invalid quotes (JSON requires double quotes `"`, not single quotes `\'`).',
                ],
                codeExample: "import json\ntry:\n    data = json.loads(raw_text)\nexcept json.JSONDecodeError as e:\n    print(f\"Malformed JSON: {e}\")",
                tags: ['python', 'json', 'jsondecodeerror'],
                confidence: 0.98,
                titleIt: 'JSONDecodeError: Dati JSON Non Validi o Malformati',
                explanationIt: 'La decodifica `json.loads()` è fallita poiché la stringa in ingresso non rispetta lo standard JSON o contiene HTML (es. pagina d\'errore 500 dell\'API).',
                solutionsIt: [
                    'Ispeziona il corpo della risposta prima della decodifica per accertarti che non sia HTML.',
                    'Verifica che le stringhe JSON usino doppi apici `"` e non apici singoli `\'`.',
                ],
            ),
            new ErrorRule(
                id: 'python.tab_error',
                category: 'Python Syntax',
                title: 'TabError: Inconsistent Use of Tabs and Spaces in Indentation',
                pattern: '/TabError:\\s*inconsistent use of tabs and spaces in indentation/i',
                explanation: 'Python forbids mixing tab characters and space characters within the same block indentation. PEP 8 standardizes on 4 spaces per indentation level.',
                solutions: [
                    'Configure your IDE / text editor to convert Tabs to 4 spaces automatically.',
                    'Run a code formatter such as `ruff format` or `black` to normalize all indentation.',
                    'In VS Code or PyCharm, enable whitespace visualization to identify stray tab characters.',
                ],
                codeExample: "ruff format myfile.py
# or
black myfile.py",
                tags: [
                    'python',
                    'syntax',
                    'indentation',
                    'pep8',
                ],
                confidence: 0.99,
                titleIt: 'TabError: Uso Incoerente di Tabulazioni e Spazi nell\'Indentazione',
                explanationIt: 'Python vieta la combinazione disomogenea di caratteri di tabulazione e spazi all\'interno dello stesso blocco di codice (PEP 8 raccomanda 4 spazi).',
                solutionsIt: [
                    'Configura l\'editor per convertire automaticamente il tasto Tab in 4 spazi.',
                    'Esegui un formatter come `ruff format` o `black` per normalizzare tutti i rientri del file.',
                    'Abilita la visualizzazione dei caratteri invisibili nel tuo IDE per individuare i tab residui.',
                ],
            ),
            new ErrorRule(
                id: 'python.name_error',
                category: 'Python Syntax',
                title: 'NameError: Name \'{match}\' Is Not Defined',
                pattern: '/NameError:\\s*name\\s*["\']?([a-zA-Z0-9_]+)["\']?\\s*is not defined/i',
                explanation: 'Variable, function, or class `{match}` was referenced before being assigned or imported in the current scope.',
                solutions: [
                    'Check for typos or letter casing mistakes in `{match}`.',
                    'Ensure the required import statement exists at the top of the file (e.g. `import {match}`).',
                    'Verify variable definition order: variables must be declared before any line that reads them.',
                ],
                codeExample: "from my_module import {match}
# Ensure variable is defined before reading:
{match} = 42",
                tags: [
                    'python',
                    'scope',
                    'nameerror',
                    'variables',
                ],
                confidence: 0.99,
                titleIt: 'NameError: Nome \'{match}\' Non Definito',
                explanationIt: 'La variabile, funzione o classe `{match}` è stata utilizzata prima di essere assegnata o importata nello scope corrente.',
                solutionsIt: [
                    'Verifica l\'ortografia o differenze tra maiuscole e minuscole nel nome `{match}`.',
                    'Assicurati che l\'importazione necessaria sia presente in cima al file (es. `import {match}`).',
                    'Controlla l\'ordine di esecuzione: la variabile deve essere valorizzata prima dell\'accesso.',
                ],
            ),
            new ErrorRule(
                id: 'python.circular_import',
                category: 'Python Environment & Imports',
                title: 'ImportError: Circular Import Involving \'{match}\' from Module \'{2}\'',
                pattern: '/ImportError:\\s*cannot import name\\s*["\']?([a-zA-Z0-9_.]+)["\']?\\s*from partially initialized module\\s*["\']?([a-zA-Z0-9_.]+)["\']?.*circular import/i',
                explanation: 'Module `{2}` is being imported before it has finished initializing because `{match}` imports `{2}` while `{2}` imports `{match}` (circular dependency).',
                solutions: [
                    'Move the import inside the specific function or method where it is needed (lazy import).',
                    'Refactor shared types, constants, or models into a separate third module that both files can import.',
                    'Use `from __future__ import annotations` (Python 3.7+) to avoid runtime evaluation of type annotations.',
                ],
                codeExample: "def get_service():
    # Lazy import inside function scope to break circular dependency:
    from {2} import {match}
    return {match}()",
                tags: [
                    'python',
                    'imports',
                    'circular-import',
                ],
                confidence: 0.99,
                titleIt: 'ImportError: Dipendenza Circolare tra \'{match}\' e il Modulo \'{2}\'',
                explanationIt: 'Il modulo `{2}` viene importato prima di aver terminato l\'inizializzazione poiché `{match}` e `{2}` si importano a vicenda ricorsivamente.',
                solutionsIt: [
                    'Sposta l\'istruzione `import` all\'interno della specifica funzione o metodo che la utilizza (lazy import).',
                    'Estrai modelli, costanti o classi condivise in un terzo modulo indipendente.',
                    'Abilita `from __future__ import annotations` per evitare la risoluzione a runtime dei type hint.',
                ],
            ),
            new ErrorRule(
                id: 'python.attempted_relative_import',
                category: 'Python Environment & Imports',
                title: 'ImportError: Attempted Relative Import with No Known Parent Package',
                pattern: '/ImportError:\\s*attempted relative import (?:with no known parent package|beyond top-level package)/i',
                explanation: 'A script using relative imports (`from . import ...` or `from .. import ...`) was executed directly as a standalone file (`__name__ == "__main__"`), where package context is undefined.',
                solutions: [
                    'Run the script as a module from your project root: `python -m my_package.my_module`.',
                    'Convert relative imports to absolute imports: `from my_package.submodule import my_func`.',
                    'Add project root to `PYTHONPATH`: `export PYTHONPATH=.` before running scripts.',
                ],
                codeExample: "python -m my_package.submodule  # Run as module from project root",
                tags: [
                    'python',
                    'imports',
                    'package',
                    'relative-import',
                ],
                confidence: 0.99,
                titleIt: 'ImportError: Tentativo di Import Relativo Senza Package Principale',
                explanationIt: 'Uno script con import relativi (`from . import ...` o `from .. import ...`) è stato eseguito direttamente come file standalone invece che come modulo di package.',
                solutionsIt: [
                    'Esegui lo script come modulo dalla root del progetto: `python -m nome_package.nome_script`.',
                    'Converti gli import relativi in import assoluti: `from nome_package.submodule import mia_funzione`.',
                    'Imposta la variabile d\'ambiente `PYTHONPATH=.` prima dell\'avvio.',
                ],
            ),
            new ErrorRule(
                id: 'python.type_error_not_callable',
                category: 'Python Types & Runtime',
                title: 'TypeError: \'{match}\' Object Is Not Callable',
                pattern: '/TypeError:\\s*["\']?([a-zA-Z0-9_.]+)["\']?\\s*object is not callable/i',
                explanation: 'Attempted to call `{match}` with parentheses `(...)`, but it is a non-callable object (such as a string, list, dictionary, int, or `@property`). Often caused by variable shadowing or using `()` instead of `[]` for indexing.',
                solutions: [
                    'Check if a variable name shadows a built-in or function name (e.g. `list = [1, 2]` followed by `list(...)`).',
                    'Use square brackets `[]` instead of parentheses `()` when indexing lists, dicts, or tuples.',
                    'Remove parentheses if accessing a `@property` attribute.',
                ],
                codeExample: "# Correct indexing:
item = items[0]  # square brackets, not items(0)
# Property access without parentheses:
value = user.full_name",
                tags: [
                    'python',
                    'types',
                    'callable',
                    'indexing',
                ],
                confidence: 0.99,
                titleIt: 'TypeError: L\'Oggetto \'{match}\' Non è Invocabile (Not Callable)',
                explanationIt: 'È stato tentato di chiamare `{match}` con parentesi tonde `(...)`, ma l\'oggetto non è una funzione né un metodo (è ad esempio una stringa, lista, intero o proprietà).',
                solutionsIt: [
                    'Verifica che una variabile locale non sovrascriva una funzione o tipo built-in (es. `list = [1, 2]`).',
                    'Usa le parentesi quadre `[]` anziché tonde `()` per accedere a elementi di liste o dizionari.',
                    'Rimuovi le parentesi se l\'attributo è decorato con `@property`.',
                ],
            ),
            new ErrorRule(
                id: 'python.type_error_unhashable',
                category: 'Python Types & Runtime',
                title: 'TypeError: Unhashable Type: \'{match}\'',
                pattern: '/TypeError:\\s*unhashable type:\\s*["\']?([a-zA-Z0-9_.]+)["\']?/i',
                explanation: 'Attempted to use a mutable object of type `{match}` (such as `list`, `dict`, or `set`) as a dictionary key or as an element of a `set`. Only immutable hashable objects can be hashed.',
                solutions: [
                    'Convert mutable lists to immutable tuples: `tuple(my_list)` before using as dict keys or set items.',
                    'Use `frozenset` instead of a mutable `set` if storing sets as keys.',
                    'Use primitive immutable identifiers (strings, integers, UUIDs) as dictionary keys.',
                ],
                codeExample: "cache = {}
key = tuple(my_list)  # Tuples are immutable and hashable
cache[key] = result",
                tags: [
                    'python',
                    'types',
                    'hashable',
                    'dict',
                    'set',
                ],
                confidence: 0.99,
                titleIt: 'TypeError: Tipo Non Hashable: \'{match}\'',
                explanationIt: 'È stato tentato di usare un oggetto mutabile di tipo `{match}` (come `list`, `dict` o `set`) come chiave di dizionario o elemento di un `set`. Solo gli oggetti immutabili con hash sono consentiti.',
                solutionsIt: [
                    'Converti le liste in tuple immutabili con `tuple(mia_lista)` prima di usarle come chiavi.',
                    'Usa `frozenset` al posto di `set` se devi memorizzare insiemi come elementi di set o dict.',
                    'Utilizza identificatori primitivi immutabili (interi, stringhe, UUID) come chiavi di dizionario.',
                ],
            ),
            new ErrorRule(
                id: 'python.type_error_unsupported_operand',
                category: 'Python Types & Runtime',
                title: 'TypeError: Unsupported Operand Type(s) for {match}: \'{2}\' and \'{3}\'',
                pattern: '/TypeError:\\s*unsupported operand type\\(s\\) for\\s*([^:]+):\\s*["\']?([^"\']+)["\']?\\s*and\\s*["\']?([^"\']+)["\']?/i',
                explanation: 'Binary operator `{match}` was evaluated between incompatible types `{2}` and `{3}` (such as adding an `int` and `str`, or performing arithmetic with `NoneType`).',
                solutions: [
                    'Cast operand values explicitly using `int()`, `float()`, or `str()`.',
                    'Guard against `NoneType` values before mathematical operations: `if val is not None:`.',
                    'Use f-strings `f"{val1}{val2}"` instead of `+` concatenation for string formatting.',
                ],
                codeExample: "total = count + int(raw_input)  # Explicit cast
# Guard against None:
value = (price or 0.0) * quantity",
                tags: [
                    'python',
                    'types',
                    'operands',
                    'typeerror',
                ],
                confidence: 0.99,
                titleIt: 'TypeError: Operando Non Supportato per {match}: \'{2}\' e \'{3}\'',
                explanationIt: 'L\'operatore binario `{match}` è stato eseguito tra tipi incompatibili `{2}` e `{3}` (ad esempio addizione tra `int` e `str`, o calcolo su valori `NoneType`).',
                solutionsIt: [
                    'Esegui il casting esplicito con `int()`, `float()` o `str()`.',
                    'Verifica che le variabili non siano `None` prima del calcolo: `if val is not None:`.',
                    'Usa f-strings `f"{a}{b}"` anziché concatenare con `+`.',
                ],
            ),
            new ErrorRule(
                id: 'python.type_error_unexpected_keyword',
                category: 'Python Types & Runtime',
                title: 'TypeError: \'{match}\' Got Unexpected Keyword Argument \'{2}\'',
                pattern: '/TypeError:\\s*([a-zA-Z0-9_.]+\\(\\)|[a-zA-Z0-9_.]+)\\s+got an unexpected keyword argument\\s*["\']?([a-zA-Z0-9_]+)["\']?/i',
                explanation: 'Function or constructor `{match}` was invoked with keyword argument `{2}`, but its parameter signature does not accept this argument and does not capture `**kwargs`.',
                solutions: [
                    'Check the function definition and documentation for exact parameter names and typos.',
                    'Add `**kwargs` to the function definition if it needs to accept and forward arbitrary parameters.',
                    'Check library versions: parameter names may have been renamed or deprecated in newer releases.',
                ],
                codeExample: "def my_func(required_arg, **kwargs):
    # Accept arbitrary keyword arguments
    pass",
                tags: [
                    'python',
                    'functions',
                    'arguments',
                    'kwargs',
                ],
                confidence: 0.99,
                titleIt: 'TypeError: \'{match}\' Ha Ricevuto l\'Argomento Inatteso \'{2}\'',
                explanationIt: 'La funzione o classe `{match}` è stata invocata con l\'argomento nominato `{2}`, ma la sua firma non lo prevede né accetta `**kwargs`.',
                solutionsIt: [
                    'Verifica l\'ortografia esatta dell\'argomento `{2}` nella firma della funzione.',
                    'Aggiungi `**kwargs` alla definizione se la funzione deve inoltrare argomenti arbitrari.',
                    'Controlla la versione della libreria: i parametri potrebbero essere stati rinominati.',
                ],
            ),
            new ErrorRule(
                id: 'python.type_error_multiple_values_for_arg',
                category: 'Python Types & Runtime',
                title: 'TypeError: \'{match}\' Got Multiple Values for Argument \'{2}\'',
                pattern: '/TypeError:\\s*([a-zA-Z0-9_.]+\\(\\)|[a-zA-Z0-9_.]+)\\s+got multiple values for argument\\s*["\']?([a-zA-Z0-9_]+)["\']?/i',
                explanation: 'Argument `{2}` was provided both as a positional parameter and as an explicit keyword parameter in the same function call.',
                solutions: [
                    'Pass parameter `{2}` either positionally or by keyword, not both.',
                    'Verify argument ordering: preceding positional arguments might be populating `{2}` unintentionally.',
                    'Check unpack operations (`*args`, `**kwargs`) for overlapping argument names.',
                ],
                codeExample: "# Provide argument only once:
func(val1, other_param=val2)  # Not: func(val1, arg1=val1)",
                tags: [
                    'python',
                    'functions',
                    'arguments',
                    'keyword-args',
                ],
                confidence: 0.99,
                titleIt: 'TypeError: \'{match}\' Ha Ricevuto Valori Multipli per l\'Argomento \'{2}\'',
                explanationIt: 'Il parametro `{2}` è stato fornito contemporaneamente sia come argomento posizionale sia come argomento nominato (keyword) nella medesima chiamata.',
                solutionsIt: [
                    'Fornisci l\'argomento `{2}` una sola volta, o in forma posizionale o per nome.',
                    'Controlla l\'ordine dei parametri posizionali per verificare che non stiano popolando `{2}` involontariamente.',
                    'Verifica se lo spacchettamento di `**kwargs` include chiavi già passate posizionalmente.',
                ],
            ),
            new ErrorRule(
                id: 'python.attribute_error_shadowed_module',
                category: 'Python Environment & Imports',
                title: 'AttributeError: Partially Initialized Module \'{match}\' Has No Attribute \'{2}\' (Name Shadowing)',
                pattern: '/AttributeError:\\s*partially initialized module\\s*["\']?([a-zA-Z0-9_.]+)["\']?\\s*has no attribute\\s*["\']?([a-zA-Z0-9_]+)["\']?/i',
                explanation: 'A local file in your working directory has the same name as a Python standard library or third-party module (`{match}.py`), causing Python to import your local file instead of the actual package.',
                solutions: [
                    'Rename your local file (e.g. rename `requests.py` to `api_client.py`, `random.py` to `dice.py`).',
                    'Delete compiled `__pycache__` directories and any `.pyc` files in your project directory.',
                    'Avoid naming scripts `email.py`, `json.py`, `csv.py`, `socket.py`, `test.py`, or `typing.py`.',
                ],
                codeExample: "mv requests.py my_requests_test.py
rm -rf __pycache__",
                tags: [
                    'python',
                    'imports',
                    'shadowing',
                    'attributeerror',
                ],
                confidence: 0.99,
                titleIt: 'AttributeError: Modulo Parzialmente Inizializzato \'{match}\' Senza Attributo \'{2}\' (Shadowing)',
                explanationIt: 'Uno script locale nella cartella di lavoro ha lo stesso nome di una libreria standard o modulo di terze parti (`{match}.py`), inducendo Python a importare il file locale anziché il pacchetto reale.',
                solutionsIt: [
                    'Rinomina lo script locale (es. da `requests.py` a `api_client.py`).',
                    'Elimina i file binari compilati `.pyc` e la cartella `__pycache__`.',
                    'Evita di assegnare a script locali nomi come `email.py`, `json.py`, `csv.py`, `random.py`, `typing.py`.',
                ],
            ),
            new ErrorRule(
                id: 'python.type_error_abstract_class',
                category: 'Python Classes & OOP',
                title: 'TypeError: Can\'t Instantiate Abstract Class \'{match}\'',
                pattern: '/TypeError:\\s*Can\'t instantiate abstract class\\s*([a-zA-Z0-9_]+)\\s+without an implementation for abstract method(?:s)?\\s*([^.]+)/i',
                explanation: 'Class `{match}` inherits from `abc.ABC` but has not implemented one or more abstract methods `{2}` declared with `@abstractmethod`.',
                solutions: [
                    'Implement all required abstract methods in subclass `{match}`.',
                    'Ensure method signatures and names match the abstract definition exactly.',
                    'If the class is intended to remain abstract, instantiate a concrete derived subclass instead.',
                ],
                codeExample: "class ConcreteWorker(AbstractWorker):
    def execute(self) -> None:
        # Implement required abstract method
        print(\"Executing task\")",
                tags: [
                    'python',
                    'oop',
                    'abstractmethod',
                    'abc',
                ],
                confidence: 0.99,
                titleIt: 'TypeError: Impossibile Istanziare la Classe Astratta \'{match}\'',
                explanationIt: 'La classe `{match}` eredita da `abc.ABC` ma non fornisce l\'implementazione di uno o più metodi astratti `{2}` decorati con `@abstractmethod`.',
                solutionsIt: [
                    'Implementa tutti i metodi astratti richiesti nella sottoclasse concreta `{match}`.',
                    'Verifica che i nomi dei metodi e la loro firma corrispondano esattamente alla classe base astratta.',
                    'Se la classe deve rimanere concettualmente astratta, istanzia una sottoclasse concreta.',
                ],
            ),
            new ErrorRule(
                id: 'python.super_no_arguments_or_type',
                category: 'Python Classes & OOP',
                title: 'RuntimeError / TypeError: Invalid super() Invocation',
                pattern: '/RuntimeError:\\s*super\\(\\): no arguments|TypeError:\\s*super\\(type,\\s*obj\\):\\s*obj must be an instance or subtype/i',
                explanation: 'Zero-argument `super()` was called outside of a class method body, inside a comprehension, or `super(Type, obj)` was passed an object that is not a subtype of `Type`.',
                solutions: [
                    'Call `super()` inside ordinary instance or class methods that have `self` or `cls` as the first argument.',
                    'In `@staticmethod`, do not call `super()`; call parent methods directly on `ParentClass.method()`.',
                    'Avoid invoking `super()` inside generator expressions or list comprehensions within class bodies.',
                ],
                codeExample: "class Child(Parent):
    def __init__(self):
        super().__init__()  # Correct invocation inside method with self",
                tags: [
                    'python',
                    'oop',
                    'super',
                    'inheritance',
                ],
                confidence: 0.99,
                titleIt: 'RuntimeError / TypeError: Invocazione Non Valida di super()',
                explanationIt: 'La chiamata `super()` senza argomenti è stata eseguita fuori dal corpo di un metodo di classe, dentro una comprehension, oppure l\'oggetto passato non è un sottotipo valido.',
                solutionsIt: [
                    'Usa `super()` all\'interno di metodi d\'istanza o di classe che ricevono `self` o `cls` come primo argomento.',
                    'Nei metodi decorati con `@staticmethod`, non usare `super()`; invoca direttamente `ClasseGenitore.metodo()`.',
                    'Evita di chiamare `super()` all\'interno di generator o list comprehension nel corpo della classe.',
                ],
            ),
            new ErrorRule(
                id: 'python.dataclass_mutable_default',
                category: 'Python Classes & OOP',
                title: 'ValueError: Mutable Default \'{match}\' for Field \'{2}\' Is Not Allowed',
                pattern: '/ValueError:\\s*mutable default\\s*<class\\s*["\']?([a-zA-Z0-9_.]+)["\']?>\\s*for field\\s*([a-zA-Z0-9_]+)\\s*is not allowed:\\s*use default_factory/i',
                explanation: 'Python `@dataclass` prohibits using mutable objects (like `[]` or `{}`) as default field values because they would be shared across all instances of the dataclass.',
                solutions: [
                    'Import `field` from `dataclasses` and define mutable defaults with `default_factory=list` or `default_factory=dict`.',
                    'For custom mutable objects, provide a factory callable: `field(default_factory=MyClass)`.',
                ],
                codeExample: "from dataclasses import dataclass, field

@dataclass
class Config:
    tags: list[str] = field(default_factory=list)
    metadata: dict[str, str] = field(default_factory=dict)",
                tags: [
                    'python',
                    'dataclass',
                    'mutable-default',
                    'oop',
                ],
                confidence: 0.99,
                titleIt: 'ValueError: Valore di Default Mutabile \'{match}\' Non Consentito per il Campo \'{2}\'',
                explanationIt: 'La libreria `@dataclass` di Python vieta l\'assegnazione di valori di default mutabili (come `[]` o `{}`) poiché verrebbero condivisi tra tutte le istanze della classe.',
                solutionsIt: [
                    'Importa `field` da `dataclasses` e usa `field(default_factory=list)` o `field(default_factory=dict)`.',
                    'Per oggetti mutabili personalizzati, specifica una factory callable: `field(default_factory=MiaClasse)`.',
                ],
            ),
            new ErrorRule(
                id: 'python.syntax_fstring_error',
                category: 'Python Syntax',
                title: 'SyntaxError: Invalid f-string Expression',
                pattern: '/SyntaxError:\\s*f-string:\\s*(?:unmatched\\s*[\'"{}\\(\\)]|expression part cannot include a backslash|expecting)/i',
                explanation: 'In Python versions prior to 3.12, backslashes `\\` are not permitted inside f-string expression braces `{...}`. Unmatched quotes or braces also trigger this error.',
                solutions: [
                    'Assign expressions containing backslashes (like `\\n` or `\\t`) to a temporary variable before interpolating.',
                    'Escape literal curly braces with double braces `{{` and `}}`.',
                    'Upgrade to Python 3.12+ where PEP 701 removes restrictions on f-string expressions and quotes.',
                ],
                codeExample: "nl = \"\\n\"
msg = f\"Results:{nl}{nl.join(items)}\"",
                tags: [
                    'python',
                    'syntax',
                    'f-string',
                    'pep701',
                ],
                confidence: 0.99,
                titleIt: 'SyntaxError: Espressione f-string Non Valida',
                explanationIt: 'Nelle versioni di Python precedenti a 3.12, il carattere backslash `\\` non è consentito all\'interno delle parentesi graffe `{...}` di una f-string. Anche virgolette o graffe non bilanciate provocano questo errore.',
                solutionsIt: [
                    'Assegna l\'espressione con backslash (come `\\n` o `\\t`) a una variabile separata prima della f-string.',
                    'Effettua l\'escape delle parentesi graffe letterali usando doppie graffe `{{` e `}}`.',
                    'Aggiorna a Python 3.12+ in cui il PEP 701 ha rimosso le restrizioni sintattiche delle f-string.',
                ],
            ),
            new ErrorRule(
                id: 'python.eof_or_unterminated_string',
                category: 'Python Syntax',
                title: 'SyntaxError: Unexpected EOF or Unterminated String Literal',
                pattern: '/SyntaxError:\\s*(?:unexpected EOF while parsing|unterminated string literal|EOL while scanning string literal)/i',
                explanation: 'The Python parser encountered the end of a line or file before finding the matching closing parenthesis, bracket, curly brace, or string quotation mark.',
                solutions: [
                    'Check the end of multiline dicts, lists, tuples, or function calls for unclosed `)`, `]`, or `}`.',
                    'Ensure multiline strings are enclosed with triple quotes `"""` or `\'\'\'`.',
                    'Check for unescaped quotation marks inside single-line strings.',
                ],
                codeExample: "query = \"\"\"
SELECT id, name
FROM users
WHERE active = 1
\"\"\"",
                tags: [
                    'python',
                    'syntax',
                    'eof',
                    'quotes',
                ],
                confidence: 0.99,
                titleIt: 'SyntaxError: Fine File Inattesa (EOF) o Stringa Non Chiusa',
                explanationIt: 'L\'interprete Python ha raggiunto la fine del file o della riga prima di trovare la parentesi di chiusura `)`, `]`, `}` o il delimitatore di stringa corrispondente.',
                solutionsIt: [
                    'Controlla espressioni multilinea (dizionari, liste, tuple) per individuare parentesi aperte non chiuse.',
                    'Assicurati che i testi multilinea siano racchiusi da triple virgolette `"""` o `\'\'\'`.',
                    'Verifica la presenza di caratteri di escape su virgolette interne a stringhe a riga singola.',
                ],
            ),
            new ErrorRule(
                id: 'python.stop_iteration',
                category: 'Python Data Structures & Iterators',
                title: 'StopIteration: Iterator Exhausted',
                pattern: '/\\bStopIteration(?:\\s*:.*)?$/m',
                explanation: 'Calling `next(iterator)` raised `StopIteration` because the iterator has no further items and no default fallback value was provided.',
                solutions: [
                    'Provide a default fallback value as the second argument: `next(it, None)`.',
                    'Wrap the call in a `try...except StopIteration:` block when manually consuming iterators.',
                    'Prefer using standard `for item in iterator:` loops which handle `StopIteration` automatically.',
                ],
                codeExample: "# Safe retrieval with default:
first_item = next(iterator, None)",
                tags: [
                    'python',
                    'iterators',
                    'stopiteration',
                    'generator',
                ],
                confidence: 0.99,
                titleIt: 'StopIteration: Iteratore Esaurito',
                explanationIt: 'La funzione `next(iteratore)` ha sollevato `StopIteration` poiché l\'iteratore non contiene ulteriori elementi e non è stato impostato un valore di default.',
                solutionsIt: [
                    'Fornisci un valore di default come secondo argomento: `next(it, None)`.',
                    'Cattura l\'eccezione con un blocco `try...except StopIteration:` durante l\'iterazione manuale.',
                    'Usa cicli `for item in iteratore:` che gestiscono `StopIteration` internamente in modo trasparente.',
                ],
            ),
            new ErrorRule(
                id: 'python.runtime_error_generator_stop',
                category: 'Python Data Structures & Iterators',
                title: 'RuntimeError: Generator Raised StopIteration (PEP 479)',
                pattern: '/RuntimeError:\\s*generator raised StopIteration/i',
                explanation: 'Under PEP 479 (standard in Python 3.7+), raising `StopIteration` inside a generator function is transformed into a `RuntimeError` to prevent silent bugs.',
                solutions: [
                    'Use `return` to terminate a generator function cleanly instead of `raise StopIteration()`.',
                    'Catch `StopIteration` inside sub-generators if calling `next()` manually.',
                ],
                codeExample: "def my_generator():
    if finished:
        return  # Use return to stop generator, not raise StopIteration",
                tags: [
                    'python',
                    'generator',
                    'pep479',
                    'runtimeerror',
                ],
                confidence: 0.99,
                titleIt: 'RuntimeError: Il Generatore Ha Sollevato StopIteration (PEP 479)',
                explanationIt: 'In base al PEP 479 (Python 3.7+), sollevare `StopIteration` all\'interno di un generatore viene convertito in `RuntimeError` per evitare anomalie silenziose.',
                solutionsIt: [
                    'Usa l\'istruzione `return` per terminare l\'esecuzione del generatore invece di invocare `raise StopIteration()`.',
                    'Cattura `StopIteration` all\'interno della funzione se chiami `next()` manualmente su iteratori interni.',
                ],
            ),
            new ErrorRule(
                id: 'python.set_changed_size_during_iteration',
                category: 'Python Data Structures & Iterators',
                title: 'RuntimeError: Set Changed Size During Iteration',
                pattern: '/RuntimeError:\\s*Set changed size during iteration/i',
                explanation: 'A `set` was mutated (items added or removed via `.add()`, `.remove()`, or `.discard()`) while actively looping over it in a `for` loop.',
                solutions: [
                    'Iterate over a copy of the set: `for item in set(my_set):` or `for item in list(my_set):`.',
                    'Collect items to remove in a separate collection and remove them after the loop.',
                    'Use a set comprehension to build a new filtered set: `{x for x in my_set if keep(x)}`.',
                ],
                codeExample: "for item in list(my_set):
    if should_remove(item):
        my_set.remove(item)",
                tags: [
                    'python',
                    'set',
                    'iteration',
                    'runtimeerror',
                ],
                confidence: 0.99,
                titleIt: 'RuntimeError: Dimensione del Set Modificata Durante l\'Iterazione',
                explanationIt: 'Un insieme (`set`) è stato modificato (aggiungendo o rimuovendo elementi) durante l\'iterazione attiva in un ciclo `for`.',
                solutionsIt: [
                    'Itera su una copia dell\'insieme: `for item in set(mio_set):` o `for item in list(mio_set):`.',
                    'Raccogli gli elementi da eliminare in una lista separata e rimuovili dopo il ciclo.',
                    'Usa una set comprehension per generare un nuovo insieme filtrato: `{x for x in mio_set if cond(x)}`.',
                ],
            ),
            new ErrorRule(
                id: 'python.value_error_not_in_list',
                category: 'Python Data Structures & Iterators',
                title: 'ValueError: Item \'{match}\' Not in List or Substring Not Found',
                pattern: '/ValueError:\\s*(?:[\'"]([^"\']+)[\'"]\\s+is not in list|substring not found)/i',
                explanation: 'Calling `list.index(x)` or `str.index(sub)` failed because the target element `{match}` does not exist in the sequence.',
                solutions: [
                    'Check membership first: `if item in my_list: idx = my_list.index(item)`.',
                    'For strings, use `str.find()` which returns `-1` instead of raising an exception.',
                    'Wrap the lookup in a `try...except ValueError:` block.',
                ],
                codeExample: "# Safe list lookup:
if target in items:
    pos = items.index(target)
# Safe string lookup:
idx = text.find(sub)  # Returns -1 if not found",
                tags: [
                    'python',
                    'list',
                    'index',
                    'substring',
                ],
                confidence: 0.99,
                titleIt: 'ValueError: Elemento \'{match}\' Non Presente nella Lista o Sottostringa Non Trovata',
                explanationIt: 'L\'invocazione di `list.index(x)` o `str.index(sub)` è fallita poiché l\'elemento `{match}` non è presente nella sequenza.',
                solutionsIt: [
                    'Verifica preventivamente con `if elemento in lista: idx = lista.index(elemento)`.',
                    'Per le stringhe, usa `str.find()` che restituisce `-1` in caso di assenza invece di lanciare eccezioni.',
                    'Gestisci la ricerca in un blocco `try...except ValueError:`.',
                ],
            ),
            new ErrorRule(
                id: 'python.value_error_float_cast',
                category: 'Python Data Structures & Iterators',
                title: 'ValueError: Could Not Convert String to Float: \'{match}\'',
                pattern: '/ValueError:\\s*could not convert string to float:\\s*[\'"]([^"\']+)[\'"]/i',
                explanation: 'String `{match}` could not be parsed into a float because it contains non-numeric characters, currency symbols, whitespace, or a European decimal comma `,` instead of a dot `.`. ',
                solutions: [
                    'Replace decimal comma with dot: `val.replace(",", ".")`.',
                    'Strip whitespace and currency symbols: `val.strip("$ €").replace(",", ".")`.',
                    'Guard against empty strings or non-numeric inputs using a helper function.',
                ],
                codeExample: "clean_str = raw_val.strip().replace(',', '.')
number = float(clean_str)",
                tags: [
                    'python',
                    'float',
                    'cast',
                    'strings',
                ],
                confidence: 0.99,
                titleIt: 'ValueError: Impossibile Convertire la Stringa in Float: \'{match}\'',
                explanationIt: 'La stringa `{match}` non può essere convertita in numero a virgola mobile perché contiene caratteri non numerici, spazi o una virgola decimale `,` al posto del punto `.`.',
                solutionsIt: [
                    'Sostituisci la virgola decimale con il punto: `val.replace(",", ".")`.',
                    'Rimuovi spazi e simboli di valuta: `val.strip("$ €").replace(",", ".")`.',
                    'Verifica che la stringa non sia vuota prima di eseguire la conversione.',
                ],
            ),
            new ErrorRule(
                id: 'python.type_error_comparison_not_supported',
                category: 'Python Data Structures & Iterators',
                title: 'TypeError: Comparison Operator Not Supported Between \'{match}\' and \'{2}\'',
                pattern: '/TypeError:\\s*["\']?[<>]={0,1}["\']?\\s*not supported between instances of\\s*["\']?([^"\']+)["\']?\\s*and\\s*["\']?([^"\']+)["\']?/i',
                explanation: 'Attempted to compare or sort values of incompatible types `{match}` and `{2}` (e.g. comparing `int` and `str`, or `None` with any number).',
                solutions: [
                    'Filter out `None` values before sorting: `[x for x in items if x is not None]`.',
                    'Provide a key function to `sort()` or `sorted()`, e.g. `sorted(items, key=lambda x: (x is None, x))`.',
                    'Cast all items to a common type before comparison.',
                ],
                codeExample: "sorted_items = sorted(items, key=lambda x: (x is None, x))",
                tags: [
                    'python',
                    'comparison',
                    'sorting',
                    'types',
                ],
                confidence: 0.99,
                titleIt: 'TypeError: Operatore di Confronto Non Supportato tra \'{match}\' e \'{2}\'',
                explanationIt: 'È stato tentato di confrontare od ordinare elementi di tipi non confrontabili `{match}` e `{2}` (es. ordinamento di una lista contenente `None` e numeri, o stringhe e interi).',
                solutionsIt: [
                    'Filtra i valori `None` prima dell\'ordinamento: `[x for x in dati if x is not None]`.',
                    'Specifica una funzione `key` a `sorted()`: `sorted(dati, key=lambda x: (x is None, x))`.',
                    'Converti tutti gli elementi in un tipo uniforme prima del confronto.',
                ],
            ),
            new ErrorRule(
                id: 'python.type_error_join_expected_str',
                category: 'Python Data Structures & Iterators',
                title: 'TypeError: str.join() Expected str Instance, \'{match}\' Found',
                pattern: '/TypeError:\\s*sequence item \\d+:\\s*expected str instance,\\s*([a-zA-Z0-9_]+)\\s+found/i',
                explanation: '`str.join(iterable)` requires every element in the collection to be a string. Encountered an object of type `{match}` (e.g. int, float, or None).',
                solutions: [
                    'Convert all elements to strings using a generator expression: `", ".join(str(x) for x in items)`.',
                    'Use list comprehension: `", ".join([str(x) for x in items])`.',
                ],
                codeExample: "joined = \", \".join(str(x) for x in numbers)",
                tags: [
                    'python',
                    'strings',
                    'join',
                    'iterables',
                ],
                confidence: 0.99,
                titleIt: 'TypeError: str.join() Richiede Istanze str, Rilevato \'{match}\'',
                explanationIt: 'Il metodo `str.join(iterabile)` richiede che tutti gli elementi siano di tipo stringa. È stato individuato un elemento di tipo `{match}` (es. intero o None).',
                solutionsIt: [
                    'Converti ogni elemento in stringa con una generator expression: `", ".join(str(x) for x in elementi)`.',
                    'Usa una list comprehension: `", ".join([str(x) for x in elementi])`.',
                ],
            ),
            new ErrorRule(
                id: 'python.re_error_pattern',
                category: 'Python Data Structures & Iterators',
                title: 're.error: Invalid Regular Expression Pattern',
                pattern: '/re\\.error:\\s*(?:nothing to repeat|unterminated character set|bad escape|missing [()\\[\\]])/i',
                explanation: 'The regular expression passed to `re.compile()`, `re.search()`, or `re.findall()` has invalid regex syntax (e.g. unescaped `+` or `*` at the beginning of a group, unclosed brackets).',
                solutions: [
                    'Use raw strings `r"pattern"` for regular expressions to avoid escaping issues.',
                    'Use `re.escape(user_string)` when interpolating user input or literal strings into patterns.',
                    'Verify that all character classes `[...]` and groups `(...)` are balanced.',
                ],
                codeExample: "import re
# Escape user input to avoid regex syntax errors:
pattern = re.compile(rf\"\\b{re.escape(query)}\\b\")",
                tags: [
                    'python',
                    'regex',
                    're',
                    'patterns',
                ],
                confidence: 0.99,
                titleIt: 're.error: Pattern di Espressione Regolare Non Valido',
                explanationIt: 'L\'espressione regolare fornita a `re.compile()`, `re.search()` o `re.findall()` contiene errori di sintassi (es. quantificatori non preceduti da caratteri o parentesi quadre non chiuse).',
                solutionsIt: [
                    'Usa sempre raw strings `r"pattern"` per le regex per evitare problemi di escape dei backslash.',
                    'Usa `re.escape(testo_utente)` quando inserisci valori dinamici o stringhe letterali nel pattern.',
                    'Controlla il bilanciamento di parentesi tonde e quadre.',
                ],
            ),
            new ErrorRule(
                id: 'python.overflow_error',
                category: 'Python Data Structures & Iterators',
                title: 'OverflowError: Numerical Result Out of Range',
                pattern: '/OverflowError:\\s*(?:math range error|int too large to convert to float|\\(34,\\s*[\'"]Result too large[\'"]\\))/i',
                explanation: 'A mathematical calculation (e.g. `math.exp()`, float exponentiation `**`, or converting a massive integer to float) exceeded the maximum floating-point representation (~1.8e308).',
                solutions: [
                    'Use Python\'s arbitrary-precision `decimal.Decimal` module.',
                    'Work in logarithmic space using `math.log()` or `numpy.log()`.',
                    'Clamp input values to safe numerical ranges before exponentiation.',
                ],
                codeExample: "from decimal import Decimal
big_result = Decimal(base) ** Decimal(power)",
                tags: [
                    'python',
                    'math',
                    'overflow',
                    'float',
                ],
                confidence: 0.99,
                titleIt: 'OverflowError: Risultato Numerico Fuori Scala (Overflow)',
                explanationIt: 'Un\'operazione matematica (es. `math.exp()`, potenza float `**` o conversione di interi enormi a float) ha superato il limite massimo dei numeri a virgola mobile (~1.8e308).',
                solutionsIt: [
                    'Usa il modulo a precisione arbitraria `decimal.Decimal`.',
                    'Lavora nello spazio logaritmico usando `math.log()` o `numpy.log()`.',
                    'Limita i valori di input prima dell\'elevamento a potenza.',
                ],
            ),
            new ErrorRule(
                id: 'python.assertion_error',
                category: 'Python Data Structures & Iterators',
                title: 'AssertionError: Assertion Failed',
                pattern: '/\\bAssertionError(?:\\s*:\\s*(.*))?$/m',
                explanation: 'An `assert condition` statement evaluated to `False` in test suites (`pytest`, `unittest`) or runtime integrity checks.',
                solutions: [
                    'Run pytest with `-vv` to see detailed value diffs: `pytest -vv tests/`.',
                    'Provide informative failure messages: `assert actual == expected, f"Expected {expected}, got {actual}"`.',
                    'Do not use `assert` for production input validation (assertions are ignored when Python runs with `-O`).',
                ],
                codeExample: "assert actual == expected, f\"Failed: expected {expected}, got {actual}\"",
                tags: [
                    'python',
                    'testing',
                    'pytest',
                    'assert',
                ],
                confidence: 0.99,
                titleIt: 'AssertionError: Asserzione Fallita',
                explanationIt: 'Un\'istruzione `assert condizione` ha restituito `False` durante l\'esecuzione di test (`pytest`, `unittest`) o controlli di invarianti.',
                solutionsIt: [
                    'Esegui pytest con il flag `-vv` per analizzare il diff dettagliato dei valori: `pytest -vv`.',
                    'Aggiungi un messaggio descrittivo: `assert x == y, f"Atteso {y}, ottenuto {x}"`.',
                    'Non usare `assert` per validare l\'input degli utenti in produzione (le asserzioni vengono ignorate con l\'opzione `-O`).',
                ],
            ),
            new ErrorRule(
                id: 'python.memory_error',
                category: 'Python Data Structures & Iterators',
                title: 'MemoryError: System Out of Memory',
                pattern: '/\\bMemoryError(?:\\s*:.*)?$/m',
                explanation: 'The Python process exhausted available RAM when allocating a massive list, reading a huge file entirely into memory with `.read()`, or loading oversized datasets.',
                solutions: [
                    'Stream large files line by line using `for line in file:` instead of `.readlines()`.',
                    'Use generator functions with `yield` to process records lazily.',
                    'Process large tabular datasets in chunks using `pandas.read_csv(filename, chunksize=10000)`.',
                ],
                codeExample: "with open(\"large_dataset.csv\") as f:
    for line in f:
        process_line(line)",
                tags: [
                    'python',
                    'memory',
                    'generators',
                    'streaming',
                ],
                confidence: 0.99,
                titleIt: 'MemoryError: Memoria RAM di Sistema Esaurita',
                explanationIt: 'Il processo Python ha esaurito la RAM di sistema tentando di caricare un intero file gigante con `.read()`, creando liste enormi o gestendo dataset senza streaming.',
                solutionsIt: [
                    'Leggi i file riga per riga con `for line in f:` invece di caricarli con `.readlines()`.',
                    'Usa generatori con `yield` per elaborare i record in maniera lazy.',
                    'Elabora grandi dataset a blocchi tramite `pandas.read_csv(filename, chunksize=10000)`.',
                ],
            ),
            new ErrorRule(
                id: 'python.keyboard_interrupt',
                category: 'Python Data Structures & Iterators',
                title: 'KeyboardInterrupt: Execution Interrupted by User (Ctrl+C)',
                pattern: '/\\bKeyboardInterrupt(?:\\s*:.*)?$/m',
                explanation: 'The process received a `SIGINT` signal, typically triggered by pressing `Ctrl+C` in the terminal.',
                solutions: [
                    'Catch `KeyboardInterrupt` in CLI or daemon entrypoints to perform graceful shutdown and resource cleanup.',
                    'Use `signal.signal(signal.SIGINT, handler)` to intercept termination signals cleanly.',
                ],
                codeExample: "try:
    run_worker_loop()
except KeyboardInterrupt:
    print(\"Shutting down gracefully...\")
    cleanup()",
                tags: [
                    'python',
                    'cli',
                    'signal',
                    'shutdown',
                ],
                confidence: 0.99,
                titleIt: 'KeyboardInterrupt: Esecuzione Interrotta dall\'Utente (Ctrl+C)',
                explanationIt: 'Il processo ha ricevuto il segnale `SIGINT`, generalmente generato dalla combinazione di tasti `Ctrl+C` nel terminale.',
                solutionsIt: [
                    'Gestisci l\'eccezione `KeyboardInterrupt` negli script CLI o daemon per rilasciare le risorse e chiudere le connessioni in modo pulito.',
                    'Configura un signal handler con `signal.signal(signal.SIGINT, handler)` per una chiusura controllata.',
                ],
            ),
            new ErrorRule(
                id: 'python.is_a_directory_error',
                category: 'Python OS & Filesystem',
                title: 'IsADirectoryError: [Errno 21] Target Path Is a Directory',
                pattern: '/IsADirectoryError:\\s*\\[Errno 21\\]\\s*Is a directory:\\s*[\'"]?([^"\'\\r\\n]+)[\'"]?/i',
                explanation: 'Attempted to open directory `{match}` as a regular file using `open()`. Operating systems do not permit opening directory nodes for file read/write operations.',
                solutions: [
                    'Verify that `{match}` points to a specific file, not a parent directory path.',
                    'Use `pathlib.Path.is_file()` or `os.path.isfile()` before calling `open()`.',
                    'To list files in a directory, use `os.listdir()` or `pathlib.Path.iterdir()`.',
                ],
                codeExample: "from pathlib import Path
path = Path(\"{match}\")
if path.is_file():
    with open(path) as f:
        content = f.read()",
                tags: [
                    'python',
                    'filesystem',
                    'open',
                    'directory',
                    'errno21',
                ],
                confidence: 0.99,
                titleIt: 'IsADirectoryError: [Errno 21] Il Percorso Indicato è una Cartella',
                explanationIt: 'È stato tentato di aprire la cartella `{match}` come se fosse un file ordinario con `open()`. I sistemi operativi non consentono la lettura/scrittura di file su nodi di directory.',
                solutionsIt: [
                    'Verifica che `{match}` punti al percorso completo del file e non alla sola directory contenitore.',
                    'Verifica che il nodo sia un file con `pathlib.Path.is_file()` o `os.path.isfile()` prima dell\'apertura.',
                    'Per scorrere i file di una cartella, usa `os.listdir()` o `pathlib.Path.iterdir()`.',
                ],
            ),
            new ErrorRule(
                id: 'python.not_a_directory_error',
                category: 'Python OS & Filesystem',
                title: 'NotADirectoryError: [Errno 20] Target Path Component Is Not a Directory',
                pattern: '/NotADirectoryError:\\s*\\[Errno 20\\]\\s*Not a directory:\\s*[\'"]?([^"\'\\r\\n]+)[\'"]?/i',
                explanation: 'A path component inside `{match}` is an existing ordinary file rather than a directory, making it impossible to traverse down into it.',
                solutions: [
                    'Inspect the path components: ensure none of the intermediate folders are existing regular files.',
                    'Check for conflicting file names that prevent directory traversal.',
                    'Use `os.path.isdir()` or `Path.is_dir()` to check directory existence.',
                ],
                codeExample: "from pathlib import Path
folder = Path(\"{match}\").parent
if folder.is_dir():
    # Proceed safely",
                tags: [
                    'python',
                    'filesystem',
                    'path',
                    'errno20',
                ],
                confidence: 0.99,
                titleIt: 'NotADirectoryError: [Errno 20] Un Elemento del Percorso Non è una Cartella',
                explanationIt: 'Un elemento intermedio del percorso `{match}` è un file esistente anziché una cartella, impedendo l\'esplorazione del percorso.',
                solutionsIt: [
                    'Ispeziona i singoli segmenti del percorso per verificare che le cartelle intermedie non siano file regolari.',
                    'Verifica eventuali conflitti di denominazione tra file e cartelle con lo stesso nome.',
                    'Controlla con `os.path.isdir()` o `Path.is_dir()` che la cartella esista.',
                ],
            ),
            new ErrorRule(
                id: 'python.file_exists_error',
                category: 'Python OS & Filesystem',
                title: 'FileExistsError: [Errno 17] File or Directory Already Exists',
                pattern: '/FileExistsError:\\s*\\[Errno 17\\]\\s*File exists:\\s*[\'"]?([^"\'\\r\\n]+)[\'"]?/i',
                explanation: 'Attempted to create a directory with `os.mkdir()` or open an exclusive file with mode \'x\' when `{match}` already exists.',
                solutions: [
                    'Use `pathlib.Path.mkdir(parents=True, exist_ok=True)` to avoid failing when the directory already exists.',
                    'Use `os.makedirs(path, exist_ok=True)`.',
                    'For files, use write mode \'w\' instead of exclusive creation mode \'x\' if overwriting is intended.',
                ],
                codeExample: "from pathlib import Path
Path(\"{match}\").mkdir(parents=True, exist_ok=True)",
                tags: [
                    'python',
                    'filesystem',
                    'mkdir',
                    'errno17',
                ],
                confidence: 0.99,
                titleIt: 'FileExistsError: [Errno 17] File o Cartella Già Esistente',
                explanationIt: 'È stato tentato di creare una cartella con `os.mkdir()` o di aprire un file in modalità esclusiva \'x\' mentre `{match}` esiste già sul disco.',
                solutionsIt: [
                    'Usa `pathlib.Path.mkdir(parents=True, exist_ok=True)` per non sollevare eccezioni se la cartella esiste.',
                    'Usa `os.makedirs(percorso, exist_ok=True)`.',
                    'Se intendi sovrascrivere il file, usa la modalità di scrittura \'w\' al posto della modalità esclusiva \'x\'.',
                ],
            ),
            new ErrorRule(
                id: 'python.os_error_invalid_argument',
                category: 'Python OS & Filesystem',
                title: 'OSError: [Errno 22] Invalid Argument in Path or OS Operation',
                pattern: '/OSError:\\s*\\[Errno 22\\]\\s*Invalid argument(?::\\s*[\'"]?([^"\'\\r\\n]+)[\'"]?)?/i',
                explanation: 'An operating system call was passed an invalid argument. On Windows, this commonly occurs when file paths contain illegal characters (`:`, `?`, `*`, `<`, `>`, `|`, or unstripped newline characters `\\n`).',
                solutions: [
                    'Strip newline characters `\\n` and `\\r` from input strings before passing them to file functions: `path.strip()`.',
                    'Sanitize path strings by removing forbidden filesystem characters (`: ? * < > | ").',
                    'Verify file descriptor numbers if calling low-level `os.read()` or `os.write()`.',
                ],
                codeExample: "import re
clean_filename = re.sub(r'[\"\\\\/*?<>:|\\r\\n]', '_', raw_filename.strip())",
                tags: [
                    'python',
                    'os',
                    'filesystem',
                    'windows',
                    'errno22',
                ],
                confidence: 0.99,
                titleIt: 'OSError: [Errno 22] Argomento Non Valido nel Percorso o Chiamata OS',
                explanationIt: 'Una chiamata di sistema ha ricevuto un argomento non valido. Su Windows capita di frequente quando i percorsi contengono caratteri proibiti (`:`, `?`, `*`, `<`, `>`, `|` o ritorni a capo `\\n` non rimossi).',
                solutionsIt: [
                    'Rimuovi ritorni a capo `\\n` e `\\r` dalla stringa del percorso prima dell\'uso con `.strip()`.',
                    'Sanifica il nome del file rimuovendo caratteri non ammessi dal file system.',
                    'Verifica i descrittori di file se utilizzi funzioni a basso livello `os.read()` o `os.write()`.',
                ],
            ),
            new ErrorRule(
                id: 'python.unicode_encode_error',
                category: 'Python OS & Filesystem',
                title: 'UnicodeEncodeError: \'{match}\' Codec Can\'t Encode Character',
                pattern: '/UnicodeEncodeError:\\s*[\'"]([^"\']+)[\'"]\\s*codec can\'t encode character\\s*[\'"]?([^"\'\\r\\n]+)?[\'"]?\\s*in position/i',
                explanation: 'Python attempted to write or print characters that cannot be represented in the active terminal or file encoding `{match}` (very common on Windows where legacy `cp1252` or `charmap` console encodings fail on emojis or Unicode symbols).',
                solutions: [
                    'Always specify `encoding="utf-8"` when calling `open(file, "w", encoding="utf-8")`.',
                    'Set the environment variable `PYTHONUTF8=1` or `PYTHONIOENCODING=utf-8` on Windows.',
                    'Reconfigure stdout in scripts: `sys.stdout.reconfigure(encoding="utf-8")`.',
                ],
                codeExample: "# Always specify utf-8 encoding:
with open(\"output.txt\", \"w\", encoding=\"utf-8\") as f:
    f.write(text)

# Or reconfigure stdout on Windows:
import sys
sys.stdout.reconfigure(encoding='utf-8')",
                tags: [
                    'python',
                    'encoding',
                    'unicode',
                    'utf-8',
                    'windows',
                ],
                confidence: 0.99,
                titleIt: 'UnicodeEncodeError: Il Codec \'{match}\' Non Riesce a Codificare il Carattere',
                explanationIt: 'Python ha tentato di stampare o scrivere caratteri non supportati dalla codifica attiva `{match}` (comune su Windows dove la console usa `cp1252` o `charmap` e fallisce con emoji o caratteri speciali).',
                solutionsIt: [
                    'Specifica sempre `encoding="utf-8"` nelle chiamate a `open(file, "w", encoding="utf-8")`.',
                    'Imposta la variabile d\'ambiente `PYTHONUTF8=1` o `PYTHONIOENCODING=utf-8` nel sistema o terminale.',
                    'Riconfigura lo standard output con `sys.stdout.reconfigure(encoding="utf-8")`.',
                ],
            ),
            new ErrorRule(
                id: 'python.io_operation_on_closed_file',
                category: 'Python OS & Filesystem',
                title: 'ValueError: I/O Operation on Closed File',
                pattern: '/ValueError:\\s*I\\/O operation on closed file/i',
                explanation: 'Attempted to read, write, or seek on a file object after it has been closed, often caused by accessing file handles outside of a `with open(...) as f:` context manager block.',
                solutions: [
                    'Keep file read and write operations inside the indented `with open(...) as f:` block.',
                    'Read data into a variable inside the context manager before using it outside: `content = f.read()`.',
                ],
                codeExample: "with open(\"data.txt\") as f:
    content = f.read()  # Read inside the block

process(content)  # Process content outside the block",
                tags: [
                    'python',
                    'filesystem',
                    'io',
                    'contextmanager',
                ],
                confidence: 0.99,
                titleIt: 'ValueError: Operazione I/O su File Chiuso',
                explanationIt: 'È stato tentato di eseguire operazioni di lettura o scrittura su un file già chiuso, tipicamente accedendo al puntatore del file all\'esterno del blocco `with open(...) as f:`.',
                solutionsIt: [
                    'Esegui tutte le operazioni di lettura e scrittura all\'interno del blocco indentato `with open(...) as f:`.',
                    'Assegna il contenuto a una variabile locale prima di uscire dal context manager: `contenuto = f.read()`.',
                ],
            ),
            new ErrorRule(
                id: 'python.csv_field_size_limit',
                category: 'Python OS & Filesystem',
                title: 'csv.Error: Field Larger Than Field Limit (131072)',
                pattern: '/(?:_csv\\.|csv\\.)Error:\\s*field larger than field limit\\s*\\(\\d+\\)/i',
                explanation: 'The Python standard `csv` module encountered a single field larger than its default 128 KB buffer limit (131072 bytes).',
                solutions: [
                    'Increase the CSV field size limit using `csv.field_size_limit(sys.maxsize)`.',
                    'Handle large fields safely with a custom buffer size.',
                ],
                codeExample: "import csv, sys
# Increase max field limit:
csv.field_size_limit(sys.maxsize)",
                tags: [
                    'python',
                    'csv',
                    'buffer',
                    'limit',
                ],
                confidence: 0.99,
                titleIt: 'csv.Error: Campo Più Grande del Limite Consentito (131072)',
                explanationIt: 'Il modulo standard `csv` ha riscontrato un singolo campo di dimensione superiore al limite predefinito di 128 KB (131072 byte).',
                solutionsIt: [
                    'Aumenta il limite massimo del buffer CSV con `csv.field_size_limit(sys.maxsize)`.',
                    'Verifica che il file CSV non abbia virgolette aperte senza chiusura che uniscono righe multiple.',
                ],
            ),
            new ErrorRule(
                id: 'python.zipfile_bad_zip_file',
                category: 'Python OS & Filesystem',
                title: 'zipfile.BadZipFile: File Is Not a Zip File',
                pattern: '/(?:zipfile\\.)?BadZipFile:\\s*File is not a zip file/i',
                explanation: 'The `zipfile` module could not open the archive because the file is corrupted, truncated, empty (0 bytes), or is an HTML/text error page saved with a `.zip` extension.',
                solutions: [
                    'Check file size: if 0 bytes or very small, verify download completed without HTTP errors.',
                    'Verify with `zipfile.is_zipfile(filename)` before attempting extraction.',
                    'Open the file with a text editor to check if it contains an HTML error response (e.g. 403 Forbidden or 404).',
                ],
                codeExample: "import zipfile
if zipfile.is_zipfile(\"archive.zip\"):
    with zipfile.ZipFile(\"archive.zip\") as z:
        z.extractall(\"output/\")",
                tags: [
                    'python',
                    'zip',
                    'zipfile',
                    'corrupted',
                ],
                confidence: 0.99,
                titleIt: 'zipfile.BadZipFile: Il File Non è un Archivio Zip Valido',
                explanationIt: 'Il modulo `zipfile` non riesce ad aprire l\'archivio perché il file è corrotto, vuoto (0 byte) o è una pagina HTML di errore scaricata con estensione `.zip`.',
                solutionsIt: [
                    'Controlla la dimensione del file: se è di 0 byte, verifica che il download sia andato a buon fine.',
                    'Verifica la validità dell\'archivio con `zipfile.is_zipfile(file)` prima dell\'estrazione.',
                    'Apri il file con un editor di testo per escludere che contenga una risposta HTML (es. 403 o 404).',
                ],
            ),
            new ErrorRule(
                id: 'python.subprocess_called_process_error',
                category: 'Python OS & Filesystem',
                title: 'subprocess.CalledProcessError: Command \'{match}\' Returned Non-Zero Exit Status {2}',
                pattern: '/subprocess\\.CalledProcessError:\\s*Command\\s*[\'"]?([^"\'\\r\\n]+)[\'"]?\\s*returned non-zero exit status\\s*(-?\\d+)/i',
                explanation: 'A shell or external process executed via `subprocess.run(..., check=True)` or `subprocess.check_call()` terminated with an error exit code `{2}`.',
                solutions: [
                    'Capture stderr output using `subprocess.run(..., capture_output=True, text=True)` to inspect error messages.',
                    'Set `check=False` to handle non-zero return codes manually via `result.returncode`.',
                    'Verify command path, permissions, and environment variables.',
                ],
                codeExample: "import subprocess
try:
    res = subprocess.run([\"command\", \"arg\"], capture_output=True, text=True, check=True)
except subprocess.CalledProcessError as e:
    print(\"Error output:\", e.stderr)",
                tags: [
                    'python',
                    'subprocess',
                    'exit-code',
                    'shell',
                ],
                confidence: 0.99,
                titleIt: 'subprocess.CalledProcessError: Il Comando \'{match}\' Ha Restituito Exit Status {2}',
                explanationIt: 'Un comando esterno o processo shell eseguito tramite `subprocess.run(..., check=True)` è terminato con codice di errore `{2}`.',
                solutionsIt: [
                    'Cattura lo standard error con `subprocess.run(..., capture_output=True, text=True)` per ispezionare il messaggio di errore.',
                    'Imposta `check=False` per gestire manualmente il codice di uscita tramite `res.returncode`.',
                    'Verifica che il binario esista nel `PATH` e che i parametri forniti siano corretti.',
                ],
            ),
            new ErrorRule(
                id: 'python.subprocess_timeout_expired',
                category: 'Python OS & Filesystem',
                title: 'subprocess.TimeoutExpired: Command \'{match}\' Timed Out After {2} Seconds',
                pattern: '/subprocess\\.TimeoutExpired:\\s*Command\\s*[\'"]?([^"\'\\r\\n]+)[\'"]?\\s*timed out after\\s*([0-9.]+)\\s*seconds/i',
                explanation: 'The external process invoked via `subprocess.run(..., timeout=N)` did not complete within the specified timeout limit of `{2}` seconds.',
                solutions: [
                    'Increase timeout value if the command performs intensive disk I/O, compilation, or network operations.',
                    'Ensure the subprocess is not blocked waiting for interactive stdin input.',
                    'Handle `TimeoutExpired` with cleanup logic or process termination.',
                ],
                codeExample: "import subprocess
try:
    subprocess.run([\"long_task\"], timeout=60, check=True)
except subprocess.TimeoutExpired:
    print(\"Subprocess took too long and was aborted.\")",
                tags: [
                    'python',
                    'subprocess',
                    'timeout',
                    'process',
                ],
                confidence: 0.99,
                titleIt: 'subprocess.TimeoutExpired: Il Comando \'{match}\' è Andato in Timeout Dopo {2} Secondi',
                explanationIt: 'Il comando avviato con `subprocess.run(..., timeout=N)` non è terminato entro il limite di tempo specificato di `{2}` secondi.',
                solutionsIt: [
                    'Aumenta il valore di `timeout` se il comando esegue elaborazioni pesanti o trasferimenti di rete.',
                    'Assicurati che il processo esterno non sia bloccato in attesa di input da stdin (richiesta password o conferma).',
                    'Gestisci l\'eccezione `subprocess.TimeoutExpired` per terminare i processi orfani.',
                ],
            ),
            new ErrorRule(
                id: 'python.shutil_error_same_file',
                category: 'Python OS & Filesystem',
                title: 'shutil.SameFileError: Source and Destination Are the Same File',
                pattern: '/(?:shutil\\.)?(?:SameFileError|Error:\\s*Destination path\\s*[\'"][^\'"]+[\'"]\\s*already exists)/i',
                explanation: '`shutil.copyfile()` or `shutil.move()` failed because the source file and destination path resolve to the exact same filesystem file or directory.',
                solutions: [
                    'Ensure destination path differs from source path before copying.',
                    'Compare canonical absolute paths with `os.path.samefile(src, dst)` before executing the copy.',
                ],
                codeExample: "import os, shutil
if not os.path.samefile(src, dst):
    shutil.copy2(src, dst)",
                tags: [
                    'python',
                    'shutil',
                    'filesystem',
                    'copy',
                ],
                confidence: 0.99,
                titleIt: 'shutil.SameFileError: Percorso Sorgente e Destinazione Coincidono',
                explanationIt: 'L\'operazione `shutil.copyfile()` o `shutil.move()` è fallita poiché il file sorgente e il file di destinazione corrispondono allo stesso percorso sul disco.',
                solutionsIt: [
                    'Verifica che il percorso di destinazione sia differente dalla sorgente prima della copia.',
                    'Confronta i percorsi con `os.path.samefile(sorgente, destinazione)` per evitare sovrascritture identiche.',
                ],
            ),
            new ErrorRule(
                id: 'python.pickle_unpickling_error',
                category: 'Python OS & Filesystem',
                title: 'pickle.UnpicklingError: Deserialization Failed or Attribute Not Found',
                pattern: '/(?:_pickle\\.|pickle\\.)UnpicklingError|AttributeError:\\s*Can\'t get attribute\\s*[\'"]?([a-zA-Z0-9_.]+)[\'"]?\\s*on <module\\s*[\'"]?__main__[\'"]?>/i',
                explanation: '`pickle.load()` failed to deserialize data. This occurs when loading data pickled under a different class definition, loading untrusted/truncated files, or when classes pickled from `__main__` cannot be located in the current module namespace.',
                solutions: [
                    'Ensure classes serialized with pickle are defined in importable modules, not inside `__main__` or top-level scripts.',
                    'Never unpickle data from untrusted or unauthenticated sources (security vulnerability).',
                    'Prefer structured serialization formats like JSON, MessagePack, or Parquet for data persistence.',
                ],
                codeExample: "# Define classes in separate importable modules:
from my_models import DataRecord
# Or use JSON for cross-version safety:
import json
data = json.loads(payload)",
                tags: [
                    'python',
                    'pickle',
                    'serialization',
                    'security',
                ],
                confidence: 0.99,
                titleIt: 'pickle.UnpicklingError: Deserializzazione Fallita o Attributo Non Trovato',
                explanationIt: 'Il modulo `pickle` non riesce a deserializzare i dati, spesso perché le classi erano definite in `__main__` anziché in un modulo importabile, o perché la classe è stata modificata tra la serializzazione e la lettura.',
                solutionsIt: [
                    'Definisci le classi serializzate in moduli separati importabili e non direttamente nel file di avvio `__main__`.',
                    'Non deserializzare mai file pickle provenienti da fonti esterne non attendibili.',
                    'Usa formati standard sicuri come JSON o MessagePack per la persistenza a lungo termine.',
                ],
            ),
            new ErrorRule(
                id: 'python.datetime_strptime_mismatch',
                category: 'Python OS & Filesystem',
                title: 'ValueError: Time Data \'{match}\' Does Not Match Format \'{2}\'',
                pattern: '/ValueError:\\s*time data\\s*[\'"]?([^"\'\\r\\n]+)[\'"]?\\s*does not match format\\s*[\'"]?([^"\'\\r\\n]+)[\'"]?/i',
                explanation: 'The date string `{match}` does not conform to the format pattern `{2}` passed to `datetime.strptime()` (e.g. wrong date separators, missing time components, or 12h vs 24h mismatch).',
                solutions: [
                    'Align the format directives: `%Y-%m-%d` (2026-11-17), `%d/%m/%Y` (17/11/2026), `%H:%M:%S` (24h time).',
                    'Use `datetime.fromisoformat(date_str)` for ISO-8601 strings (Python 3.11+ handles `Z` and timezone offsets).',
                    'Use third-party parser `dateutil.parser.parse()` for flexible date parsing.',
                ],
                codeExample: "from datetime import datetime
# ISO-8601 parser in Python 3.11+:
dt = datetime.fromisoformat(\"2026-11-17T18:30:00Z\")",
                tags: [
                    'python',
                    'datetime',
                    'strptime',
                    'parsing',
                ],
                confidence: 0.99,
                titleIt: 'ValueError: La Data \'{match}\' Non Corrisponde al Formato \'{2}\'',
                explanationIt: 'La stringa data `{match}` non rispetta il formato `{2}` specificato in `datetime.strptime()` (es. separatori diversi, componenti mancanti o formato 12h vs 24h).',
                solutionsIt: [
                    'Allinea i codici di formattazione: `%Y-%m-%d` (2026-11-17), `%d/%m/%Y` (17/11/2026), `%H:%M:%S` (24 ore).',
                    'Usa `datetime.fromisoformat(stringa)` per stringhe in formato ISO-8601.',
                    'Usa `dateutil.parser.parse(stringa)` per analizzare formati eterogenei.',
                ],
            ),
            new ErrorRule(
                id: 'python.datetime_naive_vs_aware',
                category: 'Python OS & Filesystem',
                title: 'TypeError: Cannot Compare or Subtract Offset-Naive and Offset-Aware Datetimes',
                pattern: '/TypeError:\\s*can\'t (?:subtract|compare) offset-naive and offset-aware datetimes/i',
                explanation: 'Attempted to compare or subtract a timezone-naive `datetime` (no `tzinfo`) and a timezone-aware `datetime` (with `tzinfo`, such as UTC). Python strictly forbids operations between naive and aware datetimes.',
                solutions: [
                    'Make naive datetimes timezone-aware using `dt.replace(tzinfo=timezone.utc)` or `zoneinfo.ZoneInfo`.',
                    'Always generate aware UTC datetimes: `datetime.now(timezone.utc)`.',
                    'Strip timezone information if operating strictly in local naive time: `dt.replace(tzinfo=None)`.',
                ],
                codeExample: "from datetime import datetime, timezone
now_utc = datetime.now(timezone.utc)
# Ensure both operands are timezone-aware in UTC",
                tags: [
                    'python',
                    'datetime',
                    'timezone',
                    'typeerror',
                ],
                confidence: 0.99,
                titleIt: 'TypeError: Impossibile Confrontare o Sottrarre Datetime Naive e Aware',
                explanationIt: 'È stato tentato un calcolo o confronto tra un oggetto `datetime` privo di fuso orario (naive) e uno con fuso orario esplicito (aware, es. UTC). Python vieta operazioni miste tra i due.',
                solutionsIt: [
                    'Rendi la data aware associando il fuso: `dt.replace(tzinfo=timezone.utc)`.',
                    'Genera sempre date consapevoli del fuso orario UTC: `datetime.now(timezone.utc)`.',
                    'Rimuovi il fuso orario con `dt.replace(tzinfo=None)` se intendi lavorare esclusivamente con date naive.',
                ],
            ),
            new ErrorRule(
                id: 'python.requests_timeout',
                category: 'Python Network & Asyncio',
                title: 'requests.exceptions.Timeout: HTTP Request Timed Out',
                pattern: '/requests\\.exceptions\\.(?:Timeout|ConnectTimeout|ReadTimeout):\\s*(.*)/i',
                explanation: 'An HTTP request made with the `requests` library timed out while waiting to establish a socket connection or waiting for server response bytes.',
                solutions: [
                    'Increase connection and read timeout values: `requests.get(url, timeout=(5.0, 30.0))`.',
                    'Implement retry logic with backoff using `urllib3.util.Retry` mounted on an `HTTPAdapter`.',
                    'Check server status and firewall rules for the destination endpoint.',
                ],
                codeExample: "import requests
from requests.adapters import HTTPAdapter
from urllib3.util import Retry

s = requests.Session()
retries = Retry(total=3, backoff_factor=1, status_forcelist=[502, 503, 504])
s.mount('https://', HTTPAdapter(max_retries=retries))
res = s.get(url, timeout=(5.0, 20.0))",
                tags: [
                    'python',
                    'requests',
                    'timeout',
                    'http',
                ],
                confidence: 0.99,
                titleIt: 'requests.exceptions.Timeout: Richiesta HTTP Andata in Timeout',
                explanationIt: 'Una chiamata HTTP eseguita con la libreria `requests` ha superato il tempo limite durante la connessione TCP o la ricezione dei dati dal server.',
                solutionsIt: [
                    'Aumenta i valori di timeout di connessione e lettura: `requests.get(url, timeout=(5.0, 30.0))`.',
                    'Implementa una strategia di retry con backoff esponenziale tramite `urllib3.util.Retry` su `HTTPAdapter`.',
                    'Verifica che il server remoto sia attivo e che non vi siano firewall che bloccano la porta.',
                ],
            ),
            new ErrorRule(
                id: 'python.requests_http_error',
                category: 'Python Network & Asyncio',
                title: 'requests.exceptions.HTTPError: HTTP Client or Server Error ({match})',
                pattern: '/requests\\.exceptions\\.HTTPError:\\s*([45]\\d{2})\\s+([A-Za-z\\s]+)\\s+for url:\\s*([^\\r\\n]+)/i',
                explanation: 'Calling `response.raise_for_status()` raised an HTTP error because the remote server returned HTTP status code `{match}` (`{2}`).',
                solutions: [
                    'Inspect response body for detailed API error messages: `print(response.text)`.',
                    'For 401/403, verify API keys, Bearer tokens, or OAuth authorization headers.',
                    'For 429, respect `Retry-After` headers and throttle request rates.',
                ],
                codeExample: "res = requests.get(url)
if not res.ok:
    print(f\"API error {res.status_code}: {res.text}\")",
                tags: [
                    'python',
                    'requests',
                    'httperror',
                    'http',
                ],
                confidence: 0.99,
                titleIt: 'requests.exceptions.HTTPError: Errore Risposta HTTP ({match})',
                explanationIt: 'L\'invocazione di `response.raise_for_status()` ha sollevato un\'eccezione poiché il server remoto ha risposto con codice di stato HTTP `{match}` (`{2}`).',
                solutionsIt: [
                    'Ispeziona il corpo della risposta per visualizzare i dettagli dell\'errore dell\'API: `print(response.text)`.',
                    'Per errori 401 o 403, controlla la validità di token Bearer, API key o header di autenticazione.',
                    'Per errori 429 (Rate Limit), rispetta l\'header `Retry-After` riducendo la frequenza delle chiamate.',
                ],
            ),
            new ErrorRule(
                id: 'python.requests_too_many_redirects',
                category: 'Python Network & Asyncio',
                title: 'requests.exceptions.TooManyRedirects: Exceeded Maximum Redirect Count',
                pattern: '/requests\\.exceptions\\.TooManyRedirects:\\s*Exceeded\\s*(\\d+)\\s*redirects/i',
                explanation: 'The HTTP request encountered an infinite redirect loop (e.g. HTTP to HTTPS bouncing, login redirect cycles, or missing cookie sessions).',
                solutions: [
                    'Inspect redirect history: `for r in response.history: print(r.status_code, r.url)`.',
                    'Disable automatic redirects: `requests.get(url, allow_redirects=False)` to inspect the initial 301/302.',
                    'Ensure session cookies are preserved using `requests.Session()`.',
                ],
                codeExample: "session = requests.Session()
res = session.get(url, allow_redirects=False)
print(\"Initial redirect target:\", res.headers.get('Location'))",
                tags: [
                    'python',
                    'requests',
                    'redirects',
                    'http',
                ],
                confidence: 0.99,
                titleIt: 'requests.exceptions.TooManyRedirects: Raggiunto il Limite Massimo di Redirect',
                explanationIt: 'La richiesta HTTP è rimasta bloccata in un loop infinito di reindirizzamenti (es. rimbalzo continuo tra HTTP e HTTPS, o sessione di autenticazione non memorizzata).',
                solutionsIt: [
                    'Ispeziona la cronologia dei reindirizzamenti: `for r in res.history: print(r.status_code, r.url)`.',
                    'Disabilita i redirect automatici con `allow_redirects=False` per esaminare il primo header `Location`.',
                    'Usa `requests.Session()` per memorizzare correttamente cookie e sessioni tra un redirect e l\'altro.',
                ],
            ),
            new ErrorRule(
                id: 'python.requests_missing_schema',
                category: 'Python Network & Asyncio',
                title: 'requests.exceptions.MissingSchema: Invalid URL \'{match}\' (Missing Protocol)',
                pattern: '/requests\\.exceptions\\.MissingSchema:\\s*Invalid URL\\s*[\'"]?([^"\'\\r\\n]+)[\'"]?:\\s*No scheme supplied/i',
                explanation: 'The URL `{match}` passed to `requests.get()` or `requests.post()` is missing the protocol scheme (`http://` or `https://`).',
                solutions: [
                    'Prepend `https://` to the URL if missing: `url = f"https://{url}" if not url.startswith(("http://", "https://")) else url`.',
                    'Check environment variables: verify that API endpoint base URLs include `https://`.',
                ],
                codeExample: "if not url.startswith((\"http://\", \"https://\")):
    url = f\"https://{url}\"
res = requests.get(url)",
                tags: [
                    'python',
                    'requests',
                    'url',
                    'schema',
                ],
                confidence: 0.99,
                titleIt: 'requests.exceptions.MissingSchema: URL Non Valido \'{match}\' (Protocollo Mancante)',
                explanationIt: 'L\'URL `{match}` passato alla libreria `requests` è privo del prefisso di protocollo (`http://` o `https://`).',
                solutionsIt: [
                    'Aggiungi il protocollo `https://` all\'URL: `if not url.startswith(("http://", "https://")): url = "https://" + url`.',
                    'Verifica che le variabili d\'ambiente che definiscono gli endpoint API includano `https://`.',
                ],
            ),
            new ErrorRule(
                id: 'python.urllib_certificate_verify_failed',
                category: 'Python Network & Asyncio',
                title: 'urllib / SSL: Certificate Verification Failed',
                pattern: '/(?:urllib\\.error\\.URLError:.*|ssl\\.SSLCertVerificationError:.*)\\[SSL:\\s*CERTIFICATE_VERIFY_FAILED\\]\\s*certificate verify failed/i',
                explanation: 'Python\'s `urllib` or standard SSL context could not verify the remote server\'s TLS certificate, common on macOS when Python is installed without default CA root certificates, or behind corporate MITM proxies.',
                solutions: [
                    'On macOS, run the bundled certificates installation script: `/Applications/Python\\ 3.x/Install\\ Certificates.command`.',
                    'Install or upgrade the `certifi` package: `pip install --upgrade certifi`.',
                    'Point SSL context to certifi CA bundle: `ssl_context = ssl.create_default_context(cafile=certifi.where())`.',
                ],
                codeExample: "import ssl, urllib.request, certifi
context = ssl.create_default_context(cafile=certifi.where())
response = urllib.request.urlopen(url, context=context)",
                tags: [
                    'python',
                    'ssl',
                    'urllib',
                    'certificates',
                    'macos',
                ],
                confidence: 0.99,
                titleIt: 'urllib / SSL: Verifica del Certificato SSL Fallita',
                explanationIt: 'Il modulo `urllib` o il contesto SSL standard non riesce a verificare il certificato TLS del server remoto (frequente su macOS dopo l\'installazione di Python senza bundle CA).',
                solutionsIt: [
                    'Su macOS, esegui lo script di sistema: `/Applications/Python\\ 3.x/Install\\ Certificates.command`.',
                    'Aggiorna il pacchetto certifi con `pip install --upgrade certifi`.',
                    'Configura il contesto SSL con il bundle CA di certifi: `ssl.create_default_context(cafile=certifi.where())`.',
                ],
            ),
            new ErrorRule(
                id: 'python.connection_refused_error',
                category: 'Python Network & Asyncio',
                title: 'ConnectionRefusedError: [Errno 111 / WinError 10061] Connection Refused',
                pattern: '/ConnectionRefusedError:\\s*\\[(?:Errno 111|WinError 10061)\\]\\s*(?:Connection refused|No connection could be made)/i',
                explanation: 'Attempted to connect to a TCP server or port, but no application is actively listening on that host and port, or a firewall rejected the SYN packet.',
                solutions: [
                    'Verify that the target service (Redis, PostgreSQL, backend server) is started and running.',
                    'Check host and port configurations (e.g. `localhost` vs `127.0.0.1` vs container service name in Docker).',
                    'Verify firewall or Docker port mapping (`-p 8000:8000`).',
                ],
                codeExample: "# Check service status:
sudo systemctl status redis
# Or verify port listening:
netstat -tlpn | grep 8000",
                tags: [
                    'python',
                    'network',
                    'socket',
                    'connection-refused',
                    'errno111',
                ],
                confidence: 0.99,
                titleIt: 'ConnectionRefusedError: [Errno 111 / WinError 10061] Connessione Rifiutata',
                explanationIt: 'Tentativo di connessione a un host o porta TCP in cui nessun servizio è in ascolto attivo, oppure la richiesta è stata respinta da un firewall.',
                solutionsIt: [
                    'Verifica che il servizio di destinazione (database, Redis, server web) sia effettivamente avviato.',
                    'Controlla host e porta (es. `127.0.0.1` vs `localhost` o il nome del servizio nella rete Docker).',
                    'Controlla le porte esposte nel container Docker (`-p 8000:8000`).',
                ],
            ),
            new ErrorRule(
                id: 'python.connection_reset_or_broken_pipe',
                category: 'Python Network & Asyncio',
                title: 'ConnectionResetError / BrokenPipeError: Connection Reset by Peer or Broken Pipe',
                pattern: '/(?:ConnectionResetError:\\s*\\[Errno 104\\]\\s*Connection reset by peer|BrokenPipeError:\\s*\\[Errno 32\\]\\s*Broken pipe)/i',
                explanation: 'The remote peer or client forcibly closed or terminated the TCP connection while Python was sending or receiving data.',
                solutions: [
                    'Handle `ConnectionResetError` and `BrokenPipeError` in socket/HTTP servers with graceful disconnection logic.',
                    'In reverse proxies (Nginx), increase proxy read timeout and client body buffer size.',
                    'Verify keep-alive configurations and ensure the client is not disconnecting prematurely.',
                ],
                codeExample: "try:
    sock.sendall(data)
except (ConnectionResetError, BrokenPipeError):
    print(\"Client disconnected prematurely.\")",
                tags: [
                    'python',
                    'network',
                    'socket',
                    'broken-pipe',
                    'errno32',
                ],
                confidence: 0.99,
                titleIt: 'ConnectionResetError / BrokenPipeError: Connessione Resettata o Pipe Interrotta',
                explanationIt: 'Il client remoto o il server ha chiuso forzatamente la connessione TCP mentre il processo Python stava inviando o leggendo dati.',
                solutionsIt: [
                    'Gestisci l\'eccezione nei socket/server HTTP per chiudere pulitamente le risorse del client.',
                    'Nei reverse proxy (Nginx), aumenta `proxy_read_timeout` e la dimensione dei buffer.',
                    'Verifica che il client non stia abbandonando la connessione prima del termine della risposta.',
                ],
            ),
            new ErrorRule(
                id: 'python.socket_gaierror',
                category: 'Python Network & Asyncio',
                title: 'socket.gaierror: DNS Address Resolution Failed for Host \'{match}\'',
                pattern: '/socket\\.gaierror:\\s*\\[(?:Errno 11001|Errno -2|Errno -3)\\]\\s*(?:getaddrinfo failed|Name or service not known)(?::\\s*[\'"]?([^"\'\\r\\n]+)[\'"]?)?/i',
                explanation: 'Python failed to resolve hostname `{match}` via DNS (`getaddrinfo` failed). This occurs when the hostname is misspelled, offline, or DNS resolvers are unreachable.',
                solutions: [
                    'Check for typos in the hostname (e.g. `htps://` instead of `https://`, or extra spaces).',
                    'Test DNS resolution using `nslookup {match}` or `ping {match}` in the terminal.',
                    'In Docker containers, check `/etc/resolv.conf` or verify custom bridge network configurations.',
                ],
                codeExample: "nslookup api.example.com
# Test connectivity in Python:
import socket
socket.gethostbyname(\"example.com\")",
                tags: [
                    'python',
                    'dns',
                    'network',
                    'socket',
                    'gaierror',
                ],
                confidence: 0.99,
                titleIt: 'socket.gaierror: Risoluzione DNS Fallita per l\'Host \'{match}\'',
                explanationIt: 'Python non riesce a risolvere l\'indirizzo IP dell\'host `{match}` tramite DNS (`getaddrinfo` fallita per refuso nel dominio, assenza di connessione o server DNS non raggiungibile).',
                solutionsIt: [
                    'Verifica l\'ortografia del nome di dominio e assicurati che non vi siano spazi o caratteri errati.',
                    'Verifica la risoluzione DNS da terminale con `nslookup {match}` o `ping {match}`.',
                    'Nei container Docker, verifica la configurazione del file `/etc/resolv.conf` e della rete Docker.',
                ],
            ),
            new ErrorRule(
                id: 'python.asyncio_event_loop_error',
                category: 'Python Network & Asyncio',
                title: 'RuntimeError: Asyncio Event Loop Already Running or Closed',
                pattern: '/RuntimeError:\\s*(?:This event loop is already running|asyncio\\.run\\(\\) cannot be called from a running event loop|Event loop is closed)/i',
                explanation: 'Calling `asyncio.run()` when an event loop is already actively running (common inside Jupyter notebooks, FastAPI, or Tornado), or calling loop methods on a closed event loop.',
                solutions: [
                    'In environments with a running loop (like Jupyter notebooks), use `await coro` directly or use `nest_asyncio.apply()`.',
                    'In FastAPI or async frameworks, do not call `asyncio.run()`; use `await` or `asyncio.create_task()`.',
                    'Create a new event loop if running async code inside a separate worker thread: `asyncio.new_event_loop()`.',
                ],
                codeExample: "# Inside Jupyter or existing loop:
import nest_asyncio
nest_asyncio.apply()

# Inside async functions:
await my_coroutine()  # Do not call asyncio.run()",
                tags: [
                    'python',
                    'asyncio',
                    'eventloop',
                    'runtimeerror',
                ],
                confidence: 0.99,
                titleIt: 'RuntimeError: Event Loop Asyncio Già in Esecuzione o Chiuso',
                explanationIt: 'È stato chiamato `asyncio.run()` mentre un event loop è già in esecuzione (tipico in Jupyter notebook, FastAPI o Tornado), oppure sono state invocate operazioni su un loop chiuso.',
                solutionsIt: [
                    'In Jupyter notebook o ambienti con loop attivo, usa direttamente `await coroutine` oppure installa `nest_asyncio` (`nest_asyncio.apply()`).',
                    'In FastAPI o framework asincroni, non chiamare `asyncio.run()`; usa `await` o `asyncio.create_task()`.',
                    'Se esegui codice asincrono in un thread secondario, istanzia un nuovo loop con `asyncio.new_event_loop()`.',
                ],
            ),
            new ErrorRule(
                id: 'python.asyncio_coroutine_never_awaited',
                category: 'Python Network & Asyncio',
                title: 'RuntimeWarning: Coroutine \'{match}\' Was Never Awaited',
                pattern: '/RuntimeWarning:\\s*coroutine\\s*[\'"]?([a-zA-Z0-9_.]+)[\'"]?\\s*was never awaited/i',
                explanation: 'An async coroutine function `{match}()` was called without an `await` statement before it (or without scheduling it with `asyncio.create_task()`). The function body was not executed.',
                solutions: [
                    'Add `await` before calling the coroutine: `result = await {match}()`.',
                    'Schedule background execution using `task = asyncio.create_task({match}())`.',
                    'Ensure the enclosing function is declared with `async def`.',
                ],
                codeExample: "# Await the coroutine to execute its body:
result = await {match}()
# Or schedule in background:
task = asyncio.create_task({match}())",
                tags: [
                    'python',
                    'asyncio',
                    'coroutine',
                    'await',
                    'runtimewarning',
                ],
                confidence: 0.99,
                titleIt: 'RuntimeWarning: La Coroutine \'{match}\' Non è Stata Attesa (Never Awaited)',
                explanationIt: 'La funzione asincrona `{match}()` è stata invocata senza la parola chiave `await` (o senza schedulazione con `create_task`). Di conseguenza, il corpo della funzione non è stato eseguito.',
                solutionsIt: [
                    'Aggiungi `await` davanti all\'invocazione: `risultato = await {match}()`.',
                    'Schedula l\'esecuzione in background con `asyncio.create_task({match}())`.',
                    'Verifica che la funzione contenitrice sia dichiarata come `async def`.',
                ],
            ),
            new ErrorRule(
                id: 'python.asyncio_timeout_error',
                category: 'Python Network & Asyncio',
                title: 'TimeoutError / asyncio.TimeoutError: Async Operation Timed Out',
                pattern: '/(?:asyncio\\.exceptions\\.TimeoutError|TimeoutError:\\s*.*asyncio)/i',
                explanation: 'An asynchronous operation monitored by `asyncio.wait_for()` or `async with asyncio.timeout(N):` exceeded its allotted time limit and was cancelled.',
                solutions: [
                    'Increase timeout duration in `asyncio.wait_for(coro(), timeout=N)`.',
                    'Handle `TimeoutError` with retry logic or fallback defaults.',
                    'Ensure background tasks or sockets release locks in finally blocks.',
                ],
                codeExample: "import asyncio
try:
    async with asyncio.timeout(10.0):
        data = await fetch_remote_data()
except TimeoutError:
    print(\"Async request timed out\")",
                tags: [
                    'python',
                    'asyncio',
                    'timeout',
                    'async',
                ],
                confidence: 0.99,
                titleIt: 'TimeoutError: Operazione Asincrona Asyncio Andata in Timeout',
                explanationIt: 'Un\'operazione asincrona monitorata da `asyncio.wait_for()` o dal context manager `asyncio.timeout(N)` ha superato il tempo limite ed è stata cancellata.',
                solutionsIt: [
                    'Aumenta il tempo di timeout in `asyncio.wait_for(coro(), timeout=N)`.',
                    'Gestisci l\'eccezione `TimeoutError` implementando logica di retry o valori di fallback.',
                    'Accertati che task o lock condivisi vengano rilasciati nei blocchi `finally`.',
                ],
            ),
            new ErrorRule(
                id: 'python.aiohttp_client_connector_error',
                category: 'Python Network & Asyncio',
                title: 'aiohttp: ClientConnectorError / ServerDisconnectedError',
                pattern: '/aiohttp\\.(?:client_exceptions\\.)?(?:ClientConnectorError|ServerDisconnectedError|ClientOSError):\\s*(.*)/i',
                explanation: 'The `aiohttp` async client failed to connect to the remote host due to connection refusal, SSL verification failure, or unexpected server disconnection.',
                solutions: [
                    'Verify server URL, port, and network availability.',
                    'Use `aiohttp.TCPConnector(ssl=...)` to supply custom SSL contexts or CA certificates.',
                    'Implement reconnect logic with exponential backoff.',
                ],
                codeExample: "import aiohttp
async with aiohttp.ClientSession() as session:
    try:
        async with session.get(url, timeout=15) as resp:
            data = await resp.json()
    except aiohttp.ClientConnectorError as e:
        print(\"Connection error:\", e)",
                tags: [
                    'python',
                    'aiohttp',
                    'async',
                    'http',
                    'network',
                ],
                confidence: 0.99,
                titleIt: 'aiohttp: Errore di Connessione Client (ClientConnectorError)',
                explanationIt: 'Il client asincrono `aiohttp` non è riuscito a stabilire la connessione con il server remoto per rifiuto della connessione, errore SSL o disconnessione inattesa.',
                solutionsIt: [
                    'Verifica che l\'endpoint e la porta siano raggiungibili e corretti.',
                    'Configura un `aiohttp.TCPConnector` con contesto SSL personalizzato se necessario.',
                    'Implementa logica di riconnessione con backoff esponenziale.',
                ],
            ),
            new ErrorRule(
                id: 'python.httpx_timeout_or_status_error',
                category: 'Python Network & Asyncio',
                title: 'httpx: HTTPStatusError or Request Timeout',
                pattern: '/httpx\\.(?:HTTPStatusError|ConnectTimeout|ReadTimeout|WriteTimeout):\\s*(.*)/i',
                explanation: 'The modern HTTP client `httpx` encountered a connection timeout or raised `HTTPStatusError` after `response.raise_for_status()`.',
                solutions: [
                    'Configure explicit timeouts: `httpx.Client(timeout=httpx.Timeout(10.0, connect=5.0))`.',
                    'Handle `httpx.HTTPStatusError` and inspect `e.response.status_code` and `e.response.text`.',
                ],
                codeExample: "import httpx
timeout = httpx.Timeout(20.0, connect=5.0)
with httpx.Client(timeout=timeout) as client:
    try:
        resp = client.get(url)
        resp.raise_for_status()
    except httpx.HTTPStatusError as e:
        print(e.response.status_code, e.response.text)",
                tags: [
                    'python',
                    'httpx',
                    'timeout',
                    'http',
                ],
                confidence: 0.99,
                titleIt: 'httpx: HTTPStatusError o Timeout della Richiesta',
                explanationIt: 'Il client HTTP moderno `httpx` ha riscontrato un timeout di connessione o ha sollevato `HTTPStatusError` dopo l\'invocazione di `response.raise_for_status()`.',
                solutionsIt: [
                    'Configura timeout espliciti: `httpx.Client(timeout=httpx.Timeout(20.0, connect=5.0))`.',
                    'Intercetta `httpx.HTTPStatusError` per esaminare `e.response.status_code` e il contenuto dell\'errore.',
                ],
            ),
            new ErrorRule(
                id: 'python.smtp_authentication_error',
                category: 'Python Network & Asyncio',
                title: 'smtplib.SMTPAuthenticationError: SMTP Auth Failed',
                pattern: '/smtplib\\.(?:SMTPAuthenticationError|SMTPServerDisconnected):\\s*(.*)/i',
                explanation: 'The SMTP server rejected the provided credentials or closed the connection before or during authentication.',
                solutions: [
                    'For Gmail or Google Workspace, generate and use an App Password instead of your primary account password.',
                    'Ensure `server.starttls()` is called before `server.login()` on port 587.',
                    'Verify SMTP host, port (587 for STARTTLS, 465 for SSL), username, and password.',
                ],
                codeExample: "import smtplib
with smtplib.SMTP(\"smtp.gmail.com\", 587) as server:
    server.starttls()  # Upgrade connection to secure TLS
    server.login(username, app_password)
    server.send_message(msg)",
                tags: [
                    'python',
                    'smtp',
                    'email',
                    'authentication',
                ],
                confidence: 0.99,
                titleIt: 'smtplib.SMTPAuthenticationError: Autenticazione Server SMTP Fallita',
                explanationIt: 'Il server di posta SMTP ha respinto le credenziali fornite o ha interrotto la connessione prima del completamento dell\'autenticazione.',
                solutionsIt: [
                    'Per account Gmail/Google Workspace, genera e usa una "Password per le app" al posto della password personale.',
                    'Assicurati di invocare `server.starttls()` prima di `server.login()` sulla porta 587.',
                    'Verifica host SMTP, porta (587 per STARTTLS, 465 per SSL) e credenziali.',
                ],
            ),
            new ErrorRule(
                id: 'python.pip_externally_managed_environment',
                category: 'Python Environment & Pip',
                title: 'pip: Externally-Managed Environment (PEP 668)',
                pattern: '/error:\\s*externally-managed-environment|To install Python packages system-wide/i',
                explanation: 'Under PEP 668 on Debian, Ubuntu, Homebrew (macOS), or Fedora, running `pip install` in the system Python environment is blocked to protect OS-packaged libraries from conflicting overrides.',
                solutions: [
                    'Create and activate a project virtual environment: `python3 -m venv .venv && source .venv/bin/activate`.',
                    'Use `pipx install <package>` for CLI tools intended to be used globally.',
                    'Only as a last resort in isolated Docker containers, pass the flag: `pip install --break-system-packages <pkg>`.',
                ],
                codeExample: "python3 -m venv .venv
source .venv/bin/activate  # On Linux/macOS
# .venv\\Scripts\\activate   # On Windows
pip install mypackage",
                tags: [
                    'python',
                    'pip',
                    'pep668',
                    'virtualenv',
                    'ubuntu',
                ],
                confidence: 0.99,
                titleIt: 'pip: Ambiente Gestito Esternamente (PEP 668)',
                explanationIt: 'In base al PEP 668 su distribuzioni Linux (Debian, Ubuntu) o Homebrew su macOS, l\'installazione globale con `pip install` è bloccata per evitare corruzioni dei pacchetti di sistema del sistema operativo.',
                solutionsIt: [
                    'Crea e attiva un ambiente virtuale di progetto: `python3 -m venv .venv && source .venv/bin/activate`.',
                    'Usa `pipx install <tool>` per installare strumenti CLI eseguibili a livello globale.',
                    'Solo come ultima risorsa all\'interno di container Docker isolati, usa: `pip install --break-system-packages <pkg>`.',
                ],
            ),
            new ErrorRule(
                id: 'python.pip_failed_building_wheel',
                category: 'Python Environment & Pip',
                title: 'pip: Failed Building Wheel for Package \'{match}\'',
                pattern: '/(?:error:\\s*subprocess-exited-with-error.*Failed building wheel for\\s*([a-zA-Z0-9_-]+)|Failed building wheel for\\s*([a-zA-Z0-9_-]+))/i',
                explanation: 'pip could not compile a C/C++ or Rust native extension wheel for `{match}` because required system build tools (gcc, g++, clang, cargo, python3-dev/python3-devel) or header files are missing.',
                solutions: [
                    'Install Python C-extension headers: `sudo apt-get install python3-dev build-essential` (Ubuntu/Debian) or `sudo dnf install python3-devel gcc` (Fedora/RHEL).',
                    'Upgrade packaging tools before installing: `pip install --upgrade pip setuptools wheel`.',
                    'Check package documentation for precompiled binary wheels compatible with your OS architecture.',
                ],
                codeExample: "# Install build dependencies:
sudo apt-get update && sudo apt-get install -y build-essential python3-dev
pip install --upgrade pip setuptools wheel
pip install {match}",
                tags: [
                    'python',
                    'pip',
                    'wheel',
                    'gcc',
                    'c-extensions',
                ],
                confidence: 0.99,
                titleIt: 'pip: Compilazione del Wheel Fallita per il Pacchetto \'{match}\'',
                explanationIt: 'pip non è riuscito a compilare l\'estensione nativa in C/C++ o Rust per `{match}` a causa dell\'assenza di compilatori di sistema (gcc, clang) o degli header di sviluppo di Python.',
                solutionsIt: [
                    'Installa gli strumenti di compilazione: `sudo apt-get install build-essential python3-dev` (Ubuntu/Debian).',
                    'Aggiorna gli strumenti di packaging: `pip install --upgrade pip setuptools wheel`.',
                    'Verifica che siano disponibili wheel binari precompilati per la versione di Python e l\'architettura in uso.',
                ],
            ),
            new ErrorRule(
                id: 'python.pip_resolution_impossible',
                category: 'Python Environment & Pip',
                title: 'pip: ResolutionImpossible (Conflicting Package Dependencies)',
                pattern: '/(?:ERROR:\\s*Cannot install.*because these package versions have conflicting dependencies|pip\\._vendor\\.resolvelib\\.resolvers\\.ResolutionImpossible)/i',
                explanation: 'pip\'s dependency resolver could not find a set of package versions that satisfies all version constraints across your direct dependencies and their transitive requirements.',
                solutions: [
                    'Inspect conflicting packages named in the pip error output and relax overly strict version pins (e.g. change `==1.2.3` to `>=1.2,<2.0`).',
                    'Use `pip install --dry-run <packages>` to simulate resolution.',
                    'Consider modern dependency resolvers like `uv pip install` or `poetry update` for clearer conflict graphs.',
                ],
                codeExample: "# Relax version constraints in requirements.txt:
package_a>=2.0,<3.0
package_b>=1.5",
                tags: [
                    'python',
                    'pip',
                    'dependencies',
                    'conflicts',
                    'resolver',
                ],
                confidence: 0.99,
                titleIt: 'pip: Risoluzione delle Dipendenze Impossibile (ResolutionImpossible)',
                explanationIt: 'Il risolutore di dipendenze di pip non è riuscito a trovare una combinazione di versioni compatibile a causa di vincoli in conflitto tra i pacchetti richiesti.',
                solutionsIt: [
                    'Ispeziona i pacchetti in conflitto segnalati da pip e allenta vincoli troppo rigidi nel file `requirements.txt`.',
                    'Simula la risoluzione delle dipendenze con `pip install --dry-run <pacchetti>`.',
                    'Usa gestori moderni come `uv` (`uv pip install`) o `poetry` per analizzare l\'albero delle dipendenze.',
                ],
            ),
            new ErrorRule(
                id: 'python.dotenv_missing_env_var',
                category: 'Python Environment & Pip',
                title: 'TypeError: PathLike Expected, Got NoneType (Missing Environment Variable)',
                pattern: '/TypeError:\\s*expected str, bytes or os\\.PathLike object, not NoneType/i',
                explanation: 'A function expecting a filesystem path or configuration string received `None`, almost always caused by calling `os.getenv("VAR_NAME")` or `os.environ.get("VAR_NAME")` for an environment variable that is unset or missing from `.env`.',
                solutions: [
                    'Call `load_dotenv()` before accessing environment variables with `from dotenv import load_dotenv; load_dotenv()`.',
                    'Provide a safe fallback default: `os.getenv("DATABASE_URL", "sqlite:///app.db")`.',
                    'Verify that your `.env` file exists in the working directory and contains the required key.',
                ],
                codeExample: "import os
from dotenv import load_dotenv

load_dotenv()  # Load .env file
db_url = os.getenv(\"DATABASE_URL\", \"sqlite:///default.db\")",
                tags: [
                    'python',
                    'dotenv',
                    'env',
                    'pathlike',
                    'none',
                ],
                confidence: 0.99,
                titleIt: 'TypeError: Atteso Percorso o Stringa, Rilevato NoneType (Variabile d\'Ambiente Mancante)',
                explanationIt: 'Una funzione che richiede un percorso o una stringa ha ricevuto `None`, tipicamente causato da una chiamata a `os.getenv("NOME_VAR")` su una variabile d\'ambiente non impostata o assente dal file `.env`.',
                solutionsIt: [
                    'Invoca `load_dotenv()` prima di leggere le variabili: `from dotenv import load_dotenv; load_dotenv()`.',
                    'Fornisci un valore predefinito di fallback: `os.getenv("DATABASE_URL", "sqlite:///default.db")`.',
                    'Verifica che il file `.env` esista nella cartella principale e contenga la chiave richiesta.',
                ],
            ),
            new ErrorRule(
                id: 'python.pydantic_validation_error',
                category: 'Python Web Frameworks',
                title: 'pydantic.ValidationError: Model Validation Failed for \'{match}\'',
                pattern: '/(?:pydantic(?:_core)?(?:\\._pydantic_core)?\\.ValidationError|\\d+\\s+validation error(?:s)? for\\s*([a-zA-Z0-9_]+))/i',
                explanation: 'Input data passed to Pydantic model `{match}` failed schema validation (e.g. missing required fields, type mismatch, invalid email format, or constraint violations).',
                solutions: [
                    'Inspect error details: Pydantic prints exact field paths, failed values, and failure reasons in `.errors()`.',
                    'Provide default values or `Optional[T] = None` for non-mandatory payload fields.',
                    'Use `@field_validator` (Pydantic v2) or `@validator` (v1) with `mode="before"` to preprocess data types.',
                ],
                codeExample: "from pydantic import BaseModel, Field
from typing import Optional

class UserCreate(BaseModel):
    username: str
    email: str
    age: Optional[int] = None",
                tags: [
                    'python',
                    'pydantic',
                    'validation',
                    'fastapi',
                ],
                confidence: 0.99,
                titleIt: 'pydantic.ValidationError: Validazione Modello Fallita per \'{match}\'',
                explanationIt: 'I dati passati al modello Pydantic `{match}` non rispettano lo schema definito (campi obbligatori mancanti, tipi errati o vincoli violati).',
                solutionsIt: [
                    'Ispeziona i dettagli con `.errors()` per individuare campi e valori non conformi.',
                    'Imposta valori di default o definisci i campi non obbligatori con `Optional[T] = None`.',
                    'Usa `@field_validator(..., mode="before")` in Pydantic v2 per normalizzare i dati in ingresso.',
                ],
            ),
            new ErrorRule(
                id: 'python.fastapi_unprocessable_entity_422',
                category: 'Python Web Frameworks',
                title: 'FastAPI: 422 Unprocessable Entity (Request Body Validation Error)',
                pattern: '/(?:422 Unprocessable Entity.*Field required|detail.*(?:missing|string_type|int_parsing).*fastapi)/i',
                explanation: 'FastAPI rejected an incoming HTTP request with HTTP 422 because JSON request body, query parameters, or headers failed Pydantic validation (e.g. missing required field).',
                solutions: [
                    'Inspect the response JSON payload: FastAPI returns `{"detail": [{"loc": [...], "msg": "Field required", "type": "..."}]}`.',
                    'Verify that the client sends `Content-Type: application/json` with a valid JSON body matching the schema.',
                    'Set fields as optional: `field_name: str | None = None` in the request Pydantic model.',
                ],
                codeExample: "from pydantic import BaseModel

class ItemPayload(BaseModel):
    name: str
    description: str | None = None  # Optional field with default None",
                tags: [
                    'python',
                    'fastapi',
                    '422',
                    'validation',
                    'pydantic',
                ],
                confidence: 0.99,
                titleIt: 'FastAPI: 422 Unprocessable Entity (Errore Validazione Payload)',
                explanationIt: 'FastAPI ha respinto la richiesta HTTP con codice 422 poiché i dati inviati nel body JSON, nei parametri di query o negli header non soddisfano lo schema Pydantic.',
                solutionsIt: [
                    'Esamina la risposta JSON restituita da FastAPI: individua `loc` e `msg` per capire quale campo ha fallito.',
                    'Verifica che il client invii l\'header `Content-Type: application/json` con un body valido.',
                    'Rendi i campi facoltativi nel modello Pydantic con `campo: str | None = None`.',
                ],
            ),
            new ErrorRule(
                id: 'python.flask_working_outside_app_context',
                category: 'Python Web Frameworks',
                title: 'Flask: RuntimeError: Working Outside of Application Context',
                pattern: '/RuntimeError:\\s*Working outside of (?:application|request) context/i',
                explanation: 'Attempted to access Flask context-bound objects (such as `current_app`, `g`, database models, or `request`) outside of an active HTTP request or without an application context in CLI scripts, tests, or background threads.',
                solutions: [
                    'Push an application context using `with app.app_context():` before interacting with the database or configuration.',
                    'In tests, use the Flask test client: `with app.test_request_context():`.',
                    'In Celery or background threads, pass explicit arguments instead of relying on thread-local Flask contexts.',
                ],
                codeExample: "from my_app import create_app, db
app = create_app()

with app.app_context():
    # Database operations allowed here
    db.create_all()",
                tags: [
                    'python',
                    'flask',
                    'app-context',
                    'runtimeerror',
                ],
                confidence: 0.99,
                titleIt: 'Flask: RuntimeError: Esecuzione Fuori dal Contesto dell\'Applicazione (App Context)',
                explanationIt: 'È stato tentato di accedere a risorse contestuali di Flask (`current_app`, `g`, modelli SQLAlchemy o `request`) all\'esterno di una richiesta HTTP, ad esempio in script CLI, test o thread secondari.',
                solutionsIt: [
                    'Attiva il contesto applicativo con `with app.app_context():` prima di interagire con il database.',
                    'Nei test, usa `with app.test_request_context():` per simulare l\'ambiente di richiesta.',
                    'In task asincroni (Celery) o thread in background, passa i dati esplicitamente senza dipendere dal contesto Flask.',
                ],
            ),
            new ErrorRule(
                id: 'python.django_improperly_configured',
                category: 'Python Web Frameworks',
                title: 'django.core.exceptions.ImproperlyConfigured',
                pattern: '/(?:django\\.core\\.exceptions\\.)?ImproperlyConfigured:\\s*(.*)/i',
                explanation: 'Django encountered a critical configuration error in `settings.py`, such as missing `SECRET_KEY`, missing `DJANGO_SETTINGS_MODULE`, or misconfigured `INSTALLED_APPS` / `DATABASES`.',
                solutions: [
                    'Ensure `DJANGO_SETTINGS_MODULE` is exported in your environment: `export DJANGO_SETTINGS_MODULE=myproject.settings`.',
                    'Set `SECRET_KEY` in `settings.py` or `.env`.',
                    'Verify all applications in `INSTALLED_APPS` are installed and have an `apps.py` configuration.',
                ],
                codeExample: "import os
os.environ.setdefault('DJANGO_SETTINGS_MODULE', 'myproject.settings')
import django
django.setup()",
                tags: [
                    'python',
                    'django',
                    'settings',
                    'improperlyconfigured',
                ],
                confidence: 0.99,
                titleIt: 'django.core.exceptions.ImproperlyConfigured: Configurazione Django Non Valida',
                explanationIt: 'Django ha rilevato un errore critico di configurazione in `settings.py` (es. `SECRET_KEY` mancante, `DJANGO_SETTINGS_MODULE` non impostato o app non registrate in `INSTALLED_APPS`).',
                solutionsIt: [
                    'Assicurati che `DJANGO_SETTINGS_MODULE` sia esportato: `export DJANGO_SETTINGS_MODULE=progetto.settings`.',
                    'Imposta una chiave segreta valida per `SECRET_KEY` nel file `settings.py` o `.env`.',
                    'Verifica che tutte le app elencate in `INSTALLED_APPS` siano installate nell\'ambiente virtuale.',
                ],
            ),
            new ErrorRule(
                id: 'python.django_does_not_exist',
                category: 'Python Web Frameworks',
                title: 'Django: \'{match}.DoesNotExist\' Matching Query Does Not Exist',
                pattern: '/(?:([a-zA-Z0-9_]+)\\.)?DoesNotExist:\\s*([a-zA-Z0-9_]+)\\s+matching query does not exist/i',
                explanation: 'Django ORM query `Model.objects.get(...)` returned 0 records for the specified filter criteria.',
                solutions: [
                    'In views, use `get_object_or_404(MyModel, pk=id)` to return an automatic HTTP 404 instead of a 500 error.',
                    'Use `MyModel.objects.filter(...).first()` which returns `None` instead of raising `DoesNotExist`.',
                    'Catch the exception explicitly: `except MyModel.DoesNotExist:`.',
                ],
                codeExample: "from django.shortcuts import get_object_or_404

# Safe lookup returning 404 on absence:
user = get_object_or_404(User, id=user_id)
# Or using filter().first():
user = User.objects.filter(id=user_id).first()",
                tags: [
                    'python',
                    'django',
                    'orm',
                    'doesnotexist',
                ],
                confidence: 0.99,
                titleIt: 'Django: \'{match}.DoesNotExist\' Nessun Record Trovato per la Query',
                explanationIt: 'La query Django ORM `Modello.objects.get(...)` non ha trovato alcun record corrispondente ai criteri specificati.',
                solutionsIt: [
                    'Nelle viste, usa `get_object_or_404(Modello, pk=id)` per restituire un errore HTTP 404 al client anziché un errore 500.',
                    'Usa `Modello.objects.filter(...).first()` che restituisce `None` invece di sollevare eccezioni.',
                    'Cattura l\'eccezione con `try...except Modello.DoesNotExist:`.',
                ],
            ),
            new ErrorRule(
                id: 'python.django_multiple_objects_returned',
                category: 'Python Web Frameworks',
                title: 'Django: MultipleObjectsReturned: get() Returned More Than One \'{match}\'',
                pattern: '/MultipleObjectsReturned:\\s*get\\(\\)\\s+returned more than one\\s*([a-zA-Z0-9_]+)/i',
                explanation: 'Calling `Model.objects.get(...)` returned multiple database rows when exactly one was expected. The lookup field lacks a unique database constraint.',
                solutions: [
                    'Use `filter(...)` instead of `get(...)` if multiple matching rows are expected: `items = MyModel.objects.filter(...)`.',
                    'Add a `unique=True` constraint or `UniqueConstraint` in model `Meta` to enforce uniqueness.',
                    'Use `.first()` to retrieve only the first matching record: `MyModel.objects.filter(...).first()`.',
                ],
                codeExample: "# If multiple records exist, retrieve list:
users = User.objects.filter(role=\"admin\")
# Or get only the first:
user = User.objects.filter(email=email).first()",
                tags: [
                    'python',
                    'django',
                    'orm',
                    'multipleobjectsreturned',
                ],
                confidence: 0.99,
                titleIt: 'Django: MultipleObjectsReturned: get() Ha Restituito Più di un Record \'{match}\'',
                explanationIt: 'Il metodo `Modello.objects.get(...)` ha restituito più righe dal database quando ne era attesa esattamente una sola (il campo filtrato non ha vincolo di unicità).',
                solutionsIt: [
                    'Usa `filter(...)` anziché `get(...)` se possono esistere record multipli: `oggetti = Modello.objects.filter(...)`.',
                    'Aggiungi il vincolo `unique=True` o `UniqueConstraint` nella definizione del modello.',
                    'Usa `.first()` se intendi estrarre solo il primo record: `Modello.objects.filter(...).first()`.',
                ],
            ),
            new ErrorRule(
                id: 'python.django_operational_error_no_such_table',
                category: 'Python Web Frameworks',
                title: 'Django: OperationalError: No Such Table \'{match}\'',
                pattern: '/(?:django\\.db\\.utils\\.)?OperationalError:\\s*(?:no such table:\\s*([a-zA-Z0-9_]+)|Table [\'"]([^\'"]+)[\'"] doesn\'t exist)/i',
                explanation: 'Django attempted to execute a SQL query on table `{match}`, but the table does not exist in the database because migrations have not been applied.',
                solutions: [
                    'Run database migrations: `python manage.py migrate`.',
                    'If new models or fields were created, generate migrations first: `python manage.py makemigrations`.',
                    'Check `python manage.py showmigrations` to inspect pending migration files.',
                ],
                codeExample: "python manage.py makemigrations
python manage.py migrate",
                tags: [
                    'python',
                    'django',
                    'migrations',
                    'sqlite',
                    'mysql',
                ],
                confidence: 0.99,
                titleIt: 'Django: OperationalError: Tabella \'{match}\' Non Presente nel Database',
                explanationIt: 'Django ha tentato di eseguire una query sulla tabella `{match}`, ma la tabella non esiste nel database poiché le migrazioni non sono state eseguite.',
                solutionsIt: [
                    'Applica le migrazioni al database: `python manage.py migrate`.',
                    'Se sono stati aggiunti o modificati modelli, genera prima i file di migrazione con `python manage.py makemigrations`.',
                    'Verifica lo stato delle migrazioni con `python manage.py showmigrations`.',
                ],
            ),
            new ErrorRule(
                id: 'python.django_csrf_verification_failed',
                category: 'Python Web Frameworks',
                title: 'Django: Forbidden (403): CSRF Verification Failed',
                pattern: '/Forbidden\\s*\\(403\\):\\s*CSRF verification failed(?:\\.\\s*Request aborted)?/i',
                explanation: 'Django rejected an HTTP POST/PUT/DELETE request because the CSRF token cookie was missing, invalid, or the request origin was not listed in `CSRF_TRUSTED_ORIGINS`.',
                solutions: [
                    'In HTML templates, add the `{% csrf_token %}` tag inside every `<form method="post">`.',
                    'When behind reverse proxies (Nginx/Traefik) or on HTTPS, add your domain to `CSRF_TRUSTED_ORIGINS = ["https://example.com"]` in `settings.py`.',
                    'For AJAX or API calls, pass the token in header `X-CSRFToken` from the `csrftoken` cookie.',
                ],
                codeExample: "# In settings.py for Django 4.0+:
CSRF_TRUSTED_ORIGINS = [\"https://example.com\", \"https://*.example.com\"]
# In HTML form:
# <form method=\"post\">{% csrf_token %}",
                tags: [
                    'python',
                    'django',
                    'csrf',
                    'security',
                    '403',
                ],
                confidence: 0.99,
                titleIt: 'Django: Accesso Negato (403): Verifica CSRF Fallita',
                explanationIt: 'Django ha respinto la richiesta POST/PUT/DELETE poiché il token CSRF è assente o non valido, oppure l\'origine della richiesta non è elencata in `CSRF_TRUSTED_ORIGINS`.',
                solutionsIt: [
                    'Nei template HTML, inserisci il tag `{% csrf_token %}` all\'interno di ogni `<form method="post">`.',
                    'Se utilizzi HTTPS o reverse proxy (Nginx), registra il dominio in `CSRF_TRUSTED_ORIGINS = ["https://tuodominio.com"]` in `settings.py`.',
                    'Nelle chiamate AJAX, invia l\'header `X-CSRFToken` valorizzato con il cookie `csrftoken`.',
                ],
            ),
            new ErrorRule(
                id: 'python.sqlalchemy_detached_instance',
                category: 'Python Database & ORM',
                title: 'SQLAlchemy: DetachedInstanceError: Instance \'{match}\' Is Not Bound to a Session',
                pattern: '/(?:sqlalchemy\\.orm\\.exc\\.)?DetachedInstanceError:\\s*Instance\\s*<([^>]+)>\\s*is not bound to a Session/i',
                explanation: 'Attempted to access a lazy-loaded relationship or expired attribute on model `{match}` after its originating SQLAlchemy database `Session` was closed.',
                solutions: [
                    'Use eager loading (`joinedload` or `selectinload`) to fetch related entities within the session: `query.options(joinedload(User.orders))`.',
                    'Configure session with `expire_on_commit=False` when creating the sessionmaker.',
                    'Keep data operations inside the session context: `with Session() as session:`.',
                ],
                codeExample: "from sqlalchemy.orm import sessionmaker, joinedload

Session = sessionmaker(bind=engine, expire_on_commit=False)
# Eager load relationships:
user = session.query(User).options(joinedload(User.profile)).first()",
                tags: [
                    'python',
                    'sqlalchemy',
                    'orm',
                    'session',
                    'detached',
                ],
                confidence: 0.99,
                titleIt: 'SQLAlchemy: DetachedInstanceError: L\'Istanza \'{match}\' Non è Associata a una Sessione',
                explanationIt: 'È stato tentato di accedere a una relazione lazy-load o a un attributo scaduto sul modello `{match}` dopo la chiusura della `Session` di SQLAlchemy.',
                solutionsIt: [
                    'Usa il caricamento eager (`joinedload` o `selectinload`) prima di chiudere la sessione: `query.options(joinedload(User.ordini))`.',
                    'Imposta `expire_on_commit=False` nella configurazione della factory `sessionmaker`.',
                    'Mantieni l\'elaborazione degli oggetti all\'interno del context manager `with Session() as session:`.',
                ],
            ),
            new ErrorRule(
                id: 'python.sqlalchemy_operational_or_integrity_error',
                category: 'Python Database & ORM',
                title: 'SQLAlchemy: OperationalError or IntegrityError',
                pattern: '/sqlalchemy\\.exc\\.(?:OperationalError|IntegrityError):\\s*(.*)/i',
                explanation: 'SQLAlchemy query execution failed due to database connectivity errors, syntax issues, or unique constraint / foreign key integrity violations.',
                solutions: [
                    'Inspect the underlying database driver error message (e.g. psycopg2, asyncpg, pymysql) printed in the traceback.',
                    'Ensure `session.rollback()` is invoked inside `except` blocks to reset transaction state.',
                    'Verify connection pool parameters (e.g. `pool_pre_ping=True`, `pool_recycle=3600`) to prevent stale connections.',
                ],
                codeExample: "engine = create_engine(DATABASE_URL, pool_pre_ping=True, pool_recycle=1800)
try:
    session.commit()
except Exception:
    session.rollback()
    raise",
                tags: [
                    'python',
                    'sqlalchemy',
                    'database',
                    'integrityerror',
                ],
                confidence: 0.99,
                titleIt: 'SQLAlchemy: Errore Operativo o di Integrità del Database (OperationalError / IntegrityError)',
                explanationIt: 'L\'esecuzione della query con SQLAlchemy è fallita per problemi di connessione al database, errori SQL o violazione di vincoli di unicità / chiavi esterne.',
                solutionsIt: [
                    'Ispeziona il messaggio del driver sottostante (psycopg2, asyncpg, pymysql) nel traceback.',
                    'Esegui sempre `session.rollback()` all\'interno dei blocchi `except` per resettare la transazione.',
                    'Configura il connection pool con `pool_pre_ping=True` ed `pool_recycle=1800` per prevenire disconnessioni silenziose.',
                ],
            ),
            new ErrorRule(
                id: 'python.sqlite3_operational_error_locked',
                category: 'Python Database & ORM',
                title: 'sqlite3.OperationalError: Database Is Locked',
                pattern: '/(?:sqlite3\\.)?OperationalError:\\s*database is locked/i',
                explanation: 'SQLite encountered concurrent write locks. Because SQLite operates file-level locking, another thread or process is actively holding an exclusive write lock.',
                solutions: [
                    'Enable Write-Ahead Logging (WAL) mode for high-concurrency read/write operations: `PRAGMA journal_mode=WAL;`.',
                    'Increase connection busy timeout: `sqlite3.connect("app.db", timeout=30.0)`.',
                    'Ensure all database transactions are committed and connections closed promptly.',
                    'For high-concurrency production deployments, migrate to PostgreSQL or MySQL.',
                ],
                codeExample: "import sqlite3
conn = sqlite3.connect(\"app.db\", timeout=20.0)
conn.execute(\"PRAGMA journal_mode=WAL;\")",
                tags: [
                    'python',
                    'sqlite',
                    'locked',
                    'concurrency',
                    'wal',
                ],
                confidence: 0.99,
                titleIt: 'sqlite3.OperationalError: Il Database SQLite è Bloccato (Database Is Locked)',
                explanationIt: 'SQLite ha rilevato un conflitto di lock concorrente. Essendo basato su file, un altro processo o thread sta occupando il lock di scrittura esclusivo.',
                solutionsIt: [
                    'Abilita la modalità WAL (Write-Ahead Logging): `conn.execute("PRAGMA journal_mode=WAL;")`.',
                    'Aumenta il timeout di attesa all\'apertura della connessione: `sqlite3.connect("db.sqlite", timeout=30.0)`.',
                    'Accertati che tutte le transazioni di scrittura eseguano `commit()` rapidamente.',
                    'Per carichi di lavoro concorrenti elevati in produzione, migra a PostgreSQL o MySQL.',
                ],
            ),
            new ErrorRule(
                id: 'python.pandas_key_error_column',
                category: 'Python Data Science & Pandas',
                title: 'Pandas: KeyError: Column \'{match}\' Not in DataFrame Columns',
                pattern: '/KeyError:\\s*["\']None of \\[[^\\]]+\\] are in the \\[(?:columns|index)\\]["\']|KeyError:\\s*["\']([^"\'\\r\\n]+)["\']\\s*#?\\s*.*pandas/i',
                explanation: 'Attempted to access column `{match}` in a Pandas DataFrame, but the column does not exist (spelling typo, leading/trailing whitespace in CSV headers, or uppercase/lowercase mismatch).',
                solutions: [
                    'Inspect existing DataFrame column names using `print(df.columns.tolist())`.',
                    'Strip whitespace from CSV column names: `df.columns = df.columns.str.strip()`.',
                    'Normalize column headers to lowercase: `df.columns = df.columns.str.lower()`.',
                ],
                codeExample: "# Clean column headers:
df.columns = df.columns.str.strip().str.lower()
# Safe column access:
if 'target_col' in df.columns:
    s = df['target_col']",
                tags: [
                    'python',
                    'pandas',
                    'dataframe',
                    'keyerror',
                    'columns',
                ],
                confidence: 0.99,
                titleIt: 'Pandas: KeyError: La Colonna \'{match}\' Non Esiste nel DataFrame',
                explanationIt: 'È stato tentato di accedere alla colonna `{match}` in un DataFrame Pandas, ma il nome non corrisponde a nessuna colonna (refusi, spazi bianchi iniziali/finali o differenze maiuscole/minuscole).',
                solutionsIt: [
                    'Stampa l\'elenco esatto delle colonne con `print(df.columns.tolist())`.',
                    'Rimuovi spazi bianchi dai nomi delle colonne con `df.columns = df.columns.str.strip()`.',
                    'Normalizza tutti i nomi di colonna in minuscolo: `df.columns = df.columns.str.lower()`.',
                ],
            ),
            new ErrorRule(
                id: 'python.pandas_setting_with_copy_warning',
                category: 'Python Data Science & Pandas',
                title: 'Pandas: SettingWithCopyWarning (Modifying a Slice Instead of Original DataFrame)',
                pattern: '/SettingWithCopyWarning:\\s*A value is trying to be set on a copy of a slice from a DataFrame/i',
                explanation: 'Attempted to assign a value using chained indexing (e.g. `df[df["A"] > 0]["B"] = 1`), where Pandas cannot determine whether the modification applies to the original DataFrame or an intermediate view/copy.',
                solutions: [
                    'Use `.loc[row_indexer, col_indexer]` for assignments: `df.loc[df["A"] > 0, "B"] = 1`.',
                    'Create an explicit independent copy using `.copy()`: `sub_df = df[df["A"] > 0].copy()`.',
                ],
                codeExample: "# Recommended assignment using .loc:
df.loc[df[\"status\"] == \"active\", \"points\"] += 10
# Or create explicit copy:
active_users = df[df[\"status\"] == \"active\"].copy()",
                tags: [
                    'python',
                    'pandas',
                    'dataframe',
                    'settingwithcopy',
                    'loc',
                ],
                confidence: 0.99,
                titleIt: 'Pandas: SettingWithCopyWarning (Modifica di una Copia Anziché del DataFrame Originale)',
                explanationIt: 'È stato assegnato un valore tramite indicizzazione concatenata (es. `df[filtro]["colonna"] = val`), situazione in cui Pandas non può garantire se la modifica impatti il DataFrame originale o una copia temporanea.',
                solutionsIt: [
                    'Usa sempre `.loc[indice_righe, nome_colonna]` per le assegnazioni: `df.loc[filtro, "colonna"] = valore`.',
                    'Se intendi lavorare su un sottoinsieme isolato, crea una copia esplicita con `.copy()`: `sub = df[filtro].copy()`.',
                ],
            ),
            new ErrorRule(
                id: 'python.pandas_value_error_ambiguous_truth',
                category: 'Python Data Science & Pandas',
                title: 'Pandas: ValueError: The Truth Value of a Series/DataFrame Is Ambiguous',
                pattern: '/ValueError:\\s*The truth value of a (?:Series|DataFrame) is ambiguous\\.\\s*Use a\\.empty,\\s*a\\.bool\\(\\),\\s*a\\.item\\(\\),\\s*a\\.any\\(\\)\\s*or\\s*a\\.all\\(\\)/i',
                explanation: 'Used standard Python boolean operators `and`, `or`, `not`, or `if df["col"]:` on a Pandas Series or DataFrame. Python expects a single scalar boolean, but Pandas contains an array of booleans.',
                solutions: [
                    'Use bitwise operators `&` (and), `|` (or), and `~` (not) with parentheses around each condition: `(df["A"] > 0) & (df["B"] < 10)`.',
                    'Use `.any()` or `.all()` if evaluating whole Series: `if df["col"].any():`.',
                    'Check for emptiness with `if df.empty:`.',
                ],
                codeExample: "# Bitwise operators with parentheses:
filtered = df[(df[\"age\"] >= 18) & (df[\"active\"] == True)]
# Check if any matches exist:
if not filtered.empty:
    process(filtered)",
                tags: [
                    'python',
                    'pandas',
                    'series',
                    'boolean',
                    'bitwise',
                ],
                confidence: 0.99,
                titleIt: 'Pandas: ValueError: Il Valore di Verità di una Serie o DataFrame è Ambiguo',
                explanationIt: 'Sono stati usati gli operatori logici standard di Python `and`, `or`, `not` o `if serie:` su un DataFrame o Serie Pandas, che contengono una collezione di booleani anziché un singolo valore scalare.',
                solutionsIt: [
                    'Usa gli operatori bitwise `&` (AND), `|` (OR) e `~` (NOT) racchiudendo ciascuna condizione tra parentesi: `(df["A"] > 0) & (df["B"] < 5)`.',
                    'Usa `.any()` o `.all()` per aggregare la serie in un unico valore di verità: `if serie.any():`.',
                    'Verifica se il DataFrame contiene righe con `if not df.empty:`.',
                ],
            ),
            new ErrorRule(
                id: 'python.numpy_value_error_broadcast',
                category: 'Python Data Science & Pandas',
                title: 'NumPy: ValueError: Operands Could Not Be Broadcast Together with Shapes',
                pattern: '/ValueError:\\s*operands could not be broadcast together with shapes\\s*(\\([0-9,\\s]*\\))\\s*(\\([0-9,\\s]*\\))/i',
                explanation: 'NumPy arrays with shapes `{match}` and `{2}` could not be combined in an element-wise operation because their dimension sizes are incompatible according to NumPy broadcasting rules.',
                solutions: [
                    'Check array shapes using `arr1.shape` and `arr2.shape`.',
                    'Reshape one of the arrays to be compatible: `arr2.reshape(-1, 1)` or add a dimension with `arr2[:, np.newaxis]`.',
                    'Use `np.transpose()` or transpose `.T` if dimension orders are inverted.',
                ],
                codeExample: "import numpy as np
# Reshape 1D array to column vector for broadcasting:
vec_col = vec[:, np.newaxis]  # shape (N, 1)
result = matrix + vec_col",
                tags: [
                    'python',
                    'numpy',
                    'broadcasting',
                    'shapes',
                    'arrays',
                ],
                confidence: 0.99,
                titleIt: 'NumPy: ValueError: Impossibile Eseguire il Broadcasting tra le Forme (Shapes)',
                explanationIt: 'Gli array NumPy con forme `{match}` e `{2}` non possono essere combinati in un\'operazione elemento per elemento poiché le loro dimensioni non rispettano le regole di broadcasting.',
                solutionsIt: [
                    'Ispeziona le dimensioni con `arr1.shape` e `arr2.shape`.',
                    'Modifica la forma dell\'array compatibile con `arr2.reshape(-1, 1)` o `arr2[:, np.newaxis]`.',
                    'Usa la trasposizione `.T` se le dimensioni delle matrici sono invertite.',
                ],
            ),
            new ErrorRule(
                id: 'python.pil_unidentified_image_error',
                category: 'Python Data Science & Pandas',
                title: 'Pillow / PIL: UnidentifiedImageError: Cannot Identify Image File \'{match}\'',
                pattern: '/(?:PIL\\.)?UnidentifiedImageError:\\s*cannot identify image file\\s*[\'"]?([^"\'\\r\\n]+)?[\'"]?/i',
                explanation: 'Pillow (PIL) could not decode the image file `{match}`. This occurs when the file is empty (0 bytes), corrupted, truncated, or is an HTML/PDF/WebP file saved with a `.jpg` or `.png` extension.',
                solutions: [
                    'Check file size and header bytes: verify the file is not 0 bytes or an HTML error page.',
                    'Ensure required Pillow image plugins are installed (e.g. `libwebp`, `libjpeg-turbo`).',
                    'Verify the image stream has not been read already: call `file_obj.seek(0)` before passing to `Image.open()`.',
                ],
                codeExample: "from PIL import Image
import io

# Reset stream pointer if loading from memory:
image_bytes.seek(0)
with Image.open(image_bytes) as img:
    img.verify()",
                tags: [
                    'python',
                    'pillow',
                    'pil',
                    'image',
                    'corrupted',
                ],
                confidence: 0.99,
                titleIt: 'Pillow / PIL: UnidentifiedImageError: Impossibile Identificare il File Immagine \'{match}\'',
                explanationIt: 'La libreria Pillow (PIL) non riesce a decodificare l\'immagine `{match}` (il file è vuoto, troncato o è una pagina HTML/documento rinominato con estensione `.jpg` o `.png`).',
                solutionsIt: [
                    'Controlla la dimensione del file: verifica che non sia di 0 byte o un errore HTML scaricato da una richiesta HTTP.',
                    'Se leggi da un oggetto buffer o stream di memoria, esegui `buffer.seek(0)` prima di chiamare `Image.open()`.',
                    'Verifica che le librerie codec native di sistema (libjpeg, libwebp) siano installate.',
                ],
            ),
        ];
    }
}
