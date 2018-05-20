'''
Antimalware Scanner
@author Marco Cesarato
'''
from Core import *

print(Const.NAME)

cprint(Const.EOL)
cprint('''
       d8888 888b     d888 888       888  .d8888b.   .d8888b.        d8888 888b    888      
      d88888 8888b   d8888 888   o   888 d88P  Y88b d88P  Y88b      d88888 8888b   888      
     d88P888 88888b.d88888 888  d8b  888 Y88b.      888    888     d88P888 88888b  888      
    d88P 888 888Y88888P888 888 d888b 888  "Y888b.   888           d88P 888 888Y88b 888      
   d88P  888 888 Y888P 888 888d88888b888     "Y88b. 888          d88P  888 888 Y88b888      
  d88P   888 888  Y8P  888 88888P Y88888       "888 888    888  d88P   888 888  Y88888      
 d8888888888 888   "   888 8888P   Y8888 Y88b  d88P Y88b  d88P d8888888888 888   Y8888      
d88P     888 888       888 888P     Y888  "Y8888P"   "Y8888P" d88P     888 888    Y888 
''' + Const.EOL +
       '                                  version ' + Const.VERSION, 'green', 'black')
cprint("                                                               ", 'black', 'green')
cprint("                               Antimalware Scanner                       ", 'black', 'green')
cprint("                            Created by Marco Cesarato                    ", 'black', 'green')
cprint("                                                               ", 'black', 'green')

# Summary init
Summary.scanned = 0
Summary.detected = 0
Summary.removed = 0
Summary.ignored = []
Summary.edited = []
Summary.quarantine = []
Summary.whitelist = []

parser = argparse.ArgumentParser()
parser.add_argument("-e", "--exploits", help="Check only exploits and not the functions", type=bool, default=False)
parser.add_argument("-l", "--log", help="Write a log file 'scanner.log' with all the operations done", type=str)
parser.add_argument("-p", "--path", help="Define the path to scan", type=str)
parser.add_argument("-s", "--scan", help="Scan only mode without check and remove malware. It also write", type=bool,
                    default=False)

ARGS = parser.parse_args()

cprint("Start scanning..." + Const.EOL, 'green')

if not empty(ARGS.path):
    if not os.path.exists(ARGS.path):
        cprint("Path not found" + Const.EOL, 'red')
    else:
        Path.SCAN = ARGS.path

Const.WHITELIST = read_csv(Path.WHITELIST)
file_remove(Path.LOGS)

cprint("Scan date: " + date(time.time()))
cprint("Scanning path: '" + Path.SCAN + "'" + Const.EOL)

# Malware Definitions
if empty(ARGS.exploits):
    Def.FUNCTIONS = {}
else:
    cprint("Exploits mode enabled")

if not empty(ARGS.scan):
    cprint("Scan mode enabled")

cprint("Mapping files...")

Const.FILES = recursive_directory(Const.ROOT)

cprint("Found " + str(len(Const.FILES)) + " files" + Const.EOL)
cprint("Checking files..." + Const.EOL)

time.sleep(0.1)

for filepath in tqdm(Const.FILES, desc="AWMSCAN", ncols=80):

    found = False # Default
    Summary.scanned += 1
    filename = os.path.basename(filepath)

    is_favicon = False
    if filename in 'favicon_' and substr(filename, -4) == '.ico' and len(filename) > 12:
        is_favicon = True

    if (substr(filename, -4) in ['.php', 'php4', 'php5', 'php7'] and (not os.path.exists(Path.QUARANTINE)) or Path.QUARANTINE in filepath) or is_favicon:
        scan = scanner(filepath);
        found = scan['found']
        pattern_found = scan['pattern']

    if found:
        cinput(Const.EOL + "Malware found ("+filepath+")! ")
        time.sleep(0.1)
    time.sleep(0.01)

time.sleep(0.1)

cprint(Const.EOL + "Scan finished!" + Const.EOL, 'green')