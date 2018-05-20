import os

from Types import Const, Path

# Costants
Const.NAME = "amwscan"
Const.VERSION = "0.3.10"
Const.ROOT = os.path.dirname(os.path.abspath(__file__))
Const.EOL = "\r\n"
Const.EOL2 = Const.EOL + Const.EOL

# Paths
Path.SCAN = Const.ROOT
Path.QUARANTINE = Const.ROOT + "/quarantine"
Path.LOGS = Const.ROOT + "/scanner.log"
Path.WHITELIST = Const.ROOT + "/scanner_whitelist.csv"
Path.LOGS_INFECTED = Const.ROOT + "/scanner_infected.log"