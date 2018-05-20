"""
Constant types in Python.
"""
__doc__ = """
Usage:
  import Types.Summary
  Summary.magic = 23    # Bind an attribute to a type ONCE
  Summary.magic = 88    # Re-bind it to a same type again
  Summary.magic = "one" # But NOT re-bind it to another type: this raises Summary._SummaryTypeError
  del Summary.magic     # Remove an named attribute
  Summary.__del__()     # Remove all attributes
"""
class Summary:
    class _SummaryTypeError(TypeError):
        pass
    def __repr__(self):
        return "Constant type definitions."
    def __setattr__(self, name, value):
        v = self.__dict__.get(name, value)
        if type(v) is not type(value):
            raise(self._SummaryTypeError, "Can't rebind %s to %s" % (type(v), type(value)))
        self.__dict__[name] = value
    def __del__(self):
        self.__dict__.clear()

import sys
sys.modules[__name__] = Summary()
