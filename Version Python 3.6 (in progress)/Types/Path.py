"""
Constant types in Python.
"""
__doc__ = """
Usage:
  import Types.Const
  Path.magic = 23    # Bind an attribute to a type ONCE
  Path.magic = 88    # Re-bind it to a same type again
  Path.magic = "one" # But NOT re-bind it to another type: this raises Path._PathTypeError
  del Path.magic     # Remove an named attribute
  Path.__del__()     # Remove all attributes
"""
class Path:
    class _PathTypeError(TypeError):
        pass
    def __repr__(self):
        return "Constant type definitions."
    def __setattr__(self, name, value):
        v = self.__dict__.get(name, value)
        if type(v) is not type(value):
            raise(self._PathTypeError, "Can't rebind %s to %s" % (type(v), type(value)))
        self.__dict__[name] = value
    def __del__(self):
        self.__dict__.clear()

import sys
sys.modules[__name__] = Path()
