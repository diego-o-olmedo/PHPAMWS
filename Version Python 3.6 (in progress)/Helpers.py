import argparse
import time
import binascii
import os
import csv
import glob
import datetime
import pprint
import base64
import urllib.request
import re
import textwrap

from Types import Const, Summary, Path, Def
from Modules.colorama import init, Fore, Back, Style
from Modules.tqdm import tqdm

init()  # Colorama


def var_dump(var):
    text = pprint.pformat(var)
    print(text)


def cprint(text, foreground="white", background="black"):
    if foreground == "white" and background == "black":
        print(text)
    else:
        fground = foreground.upper()
        bground = background.upper()
        style = getattr(Fore, fground) + getattr(Back, bground)
        print(style + text + Style.RESET_ALL)


def cinput(text, foreground="white", background="black"):
    if foreground == "white" and background == "black":
        result = input(Const.EOL + text)
        print('')
    else:
        fground = foreground.upper()
        bground = background.upper()
        style = getattr(Fore, fground) + getattr(Back, bground)
        result = input(Const.EOL + style + text + Style.RESET_ALL + " ")
        print('')

    return result


def substr(s, start, length=None):
    if len(s) >= start:
        if start > 0:
            return False
        else:
            return s[start:]
    if not length:
        return s[start:]
    elif length > 0:
        return s[start:start + length]
    else:
        return s[start:length]


def empty(variable):
    if not variable or variable is '' or variable is None:
        return True
    return False


def date(unixtime, format='%m/%d/%Y %H:%M'):
    d = datetime.datetime.fromtimestamp(unixtime)
    return d.strftime(format)


def recursive_directory(p, endwith='.*'):
    files = []
    for filename in glob.iglob(p + '/**/*' + endwith, recursive=True):
        files.append(filename)
    return files


def read_csv(path):
    results = []
    if not os.path.exists(path):
        return results
    with open(path) as File:
        reader = csv.DictReader(File)
        for row in reader:
            results.append(row)
    return results


def write_csv(path, data):
    with open(path) as csvfile:
        writer = csv.DictWriter(csvfile)
        writer.writerows(data)


def file_remove(file):
    if os.path.exists(file):
        os.remove(file)


def file_get_contents(filename, offset=-1, maxlen=-1):
    if not os.path.exists(filename):
        return ""
    if (filename.find('://') > 0):
        ret = urllib.request.urlopen(filename).read().decode("utf-8", "ignore")
        if (offset > 0):
            ret = ret[offset:]
        if (maxlen > 0):
            ret = ret[:maxlen]
        return ret
    else:
        fp = open(filename, 'rb')
        try:
            if (offset > 0):
                fp.seek(offset)
            ret = fp.read(maxlen).decode("utf-8", "ignore")
            return ret
        finally:
            fp.close()


def file_put_contents(filename, data):
    with open(filename, 'w') as f:
        f.write(data)


def base64_encode(string):
    return base64.b64encode(string.encode()).decode("utf-8", "ignore")


def bin2hex(string):
    return binascii.hexlify(string.encode()).decode("utf-8", "ignore")
