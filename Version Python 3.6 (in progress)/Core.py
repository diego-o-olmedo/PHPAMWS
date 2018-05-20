import Config
from Helpers import *

def scanner(filepath):
    found = False
    pattern_found = {}
    fc = file_get_contents(filepath)

    for key, pattern in Def.EXPLOITS.items():
        match = re.search(pattern, fc, re.IGNORECASE)
        if not empty(match):
            found = True
            match_line = match.group(0)
            lineNumber = str(len(fc.split(match_line)[0].split("\n")))
            pattern_found[key + " [line " + lineNumber + "]"] = match_line

    # Scan php commands
    contents = re.sub(r"<\?php(.*?)(?!\B\"[^\"]*)\?>(?![^\"]*\"\B)", "\1", fc,
                      re.DOTALL | re.IGNORECASE)  # Only php code
    contents = re.sub(r"\/\*.*?\*\/|\/\/.*?\n|\#.*?\n", "", contents, re.DOTALL | re.IGNORECASE)  # Remove comments
    contents = re.sub(r"('|\")[\s\r\n]*\.[\s\r\n]*('|\")", "", contents, re.DOTALL | re.IGNORECASE)  # Remove "ev". "al"
    for pattern in Def.FUNCTIONS:

        regex_pattern = r"/(" + pattern + ")[\s\r\n]*\(/i"
        match_pattern = re.search(regex_pattern, contents, re.IGNORECASE)

        regex_pattern = r"/(" + re.escape(base64_encode(pattern)) + ")[\s\r\n]*\(/i"
        match_base64 = re.search(regex_pattern, contents, re.IGNORECASE)

        end = "\\x"
        pattern_hex = bin2hex(pattern)
        pattern_hex = end.join(textwrap.wrap(pattern_hex, 2))
        pattern_hex = '\\x' + substr(pattern_hex, 0, -2);

        regex_pattern = r"/(" + re.escape(pattern_hex) + ")[\s\r\n]*\(/i"
        match_hex = re.search(regex_pattern, contents, re.IGNORECASE)

        match = match_pattern or match_base64 or match_hex or None

        if not empty(match):
            found = True
            match_line = match.group(0)
            lineNumber = str(len(fc.split(match_line)[0].split("\n")))
            pattern_found[pattern + " [line " + lineNumber + "]"] = match_line

    return {'found': found, 'pattern': pattern_found}