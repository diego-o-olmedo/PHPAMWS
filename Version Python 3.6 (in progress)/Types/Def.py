"""
Constant types in Python.
"""
__doc__ = """
Usage:
  import Types.Const
  Def.magic = 23    # Bind an attribute to a type ONCE
  Def.magic = 88    # Re-bind it to a same type again
  Def.magic = "one" # But NOT re-bind it to another type: this raises Def._DefTypeError
  del Def.magic     # Remove an named attribute
  Def.__del__()     # Remove all attributes
"""
class Def:

    FUNCTIONS = {
        "il_exec",
        "shell_exec"''',
        "eval",
        "exec",
        "create_function",
        "assert",
        "system",
        "syslog",
        "passthru",
        "dl",
        "define_syslog_variables",
        "debugger_off",
        "debugger_on",
        "stream_select",
        "parse_ini_file",
        "show_source",
        "symlink",
        "popen",
        "posix_kill",
        "posix_getpwuid",
        "posix_mkfifo",
        "posix_setpgid",
        "posix_setsid",
        "posix_setuid",
        "posix_uname",
        "proc_close",
        "proc_get_status",
        "proc_nice",
        "proc_open",
        "proc_terminate",
        "ini_alter",
        "ini_get_all",
        "ini_restore",
        "parse_ini_file",
        "inject_code",
        "apache_child_terminate",
        "apache_setenv",
        "apache_note",
        "define_syslog_variables",
        "escapeshellarg",
        "escapeshellcmd",
        "ob_start",'''
    }

    EXPLOITS = {
        "eval_chr": r"chr\s*\(\s*101\s*\)\s*\.\s*chr\s*\(\s*118\s*\)\s*\.\s*chr\s*\(\s*97\s*\)\s*\.\s*chr\s*\(\s*108\s*\)",
        # "eval_preg": "(preg_replace(_callback)?|mb_ereg_replace|preg_filter)\s*\(.+(\/|\\x2f)(e|\\x65)['\"]",
        "align": r"(\\\$\w+=[^;]*)*;\\\$\w+=@?\\\$\w+\(",
        "b374k": r"'ev'\.'al'\.'\(\"\?>",  # b374k shell
        "weevely3": r"\\\$\w=\\\$[a-zA-Z]\('',\\\$\w\);\\\$\w\(\);",  # weevely3 launcher
        "c99_launcher": r";\\\$\w+\(\\\$\w+(,\s?\\\$\w+)+\);",
    # http://bartblaze.blogspot.fr/2015/03/c99shell-not-dead.html
        # "too_many_chr": r"(chr\([\d]+\)\.){8}",  # concatenation of more than eight `chr()`
        # "concat": r"(\\\$[^\n\r]+\.){5}",  # concatenation of more than 5 words
        # "concat_with_spaces": r"(\\\$[^\\n\\r]+\. ){5}",  # concatenation of more than 5 words, with spaces
        # "var_as_func": r"\\\$_(GET|POST|COOKIE|REQUEST|SERVER)\s*\[[^\]]+\]\s*\(",
        "escaped_path": r"(\\\\x[0-9abcdef]{2}[a-z0-9.-\/]{1,4}){4,}",
        # "infected_comment": r"\/\*[a-z0-9]{5}\*\/", # usually used to detect if a file is infected yet
        "hex_char": r"\\[Xx](5[Ff])",
        "download_remote_code": r"echo\s+file_get_contents\s*\(\s*base64_url_decode\s*\(\s*@*\\\$_(GET|POST|SERVER|COOKIE|REQUEST)",
        "globals_concat": r"\\\$GLOBALS\[\\\$GLOBALS['[a-z0-9]{4,}'\]\[\d+\]\.\\\$GLOBALS\['[a-z-0-9]{4,}'\]\[\d+\].",
        "globals_assign": r"\\\$GLOBALS\['[a-z0-9]{5,}'\] = \\\$[a-z]+\d+\[\d+\]\.\\\$[a-z]+\d+\[\d+\]\.\\\$[a-z]+\d+\[\d+\]\.\\\$[a-z]+\d+\[\d+\]\.",
        # "php_long": r"^.*<\?php.{1000,}\?>.*$",
        # "base64_long": r"['\"][A-Za-z0-9+\/]{260,}={0,3}['\"]/",
        "clever_include": r"include\s*\(\s*[^\.]+\.(png|jpe?g|gif|bmp)",
        # "basedir_bypass": "curl_init\s*\(\s*[\"']file:\/\/",
        "basedir_bypass2": r"file\:file\:\/\/",  # https://www.intelligentexploit.com/view-details.html?id=8719
        "non_cprintable": r"(function|return|base64_decode).{,256}[^\\x00-\\x1F\\x7F-\\xFF]{3}",
        "double_var": r"\\\${\s*\\\${",
        "double_var2": r"\${\$[0-9a-zA-z]+}",
        "hex_var": r"\\\$\{\\\"\\\\x",  # check for ${"\xFF"}, IonCube use this method ${"\x
        "register_function": r"register_[a-z]+_function\s*\(\s*['\\\"]\s*(eval|assert|passthru|exec|include|system|shell_exec|`)",
        # https://github.com/nbs-system/php-malware-finder/issues/41
        "safemode_bypass": r"\\x00\/\.\.\/|LD_PRELOAD",
        "ioncube_loader": r"IonCube\_loader"
    }

    class _DefTypeError(TypeError):
        pass
    def __repr__(self):
        return "Constant type definitions."
    def __setattr__(self, name, value):
        v = self.__dict__.get(name, value)
        if type(v) is not type(value):
            raise(self._DefTypeError, "Can't rebind %s to %s" % (type(v), type(value)))
        self.__dict__[name] = value
    def __del__(self):
        self.__dict__.clear()


import sys
sys.modules[__name__] = Def()