"""
Constant types in Python.
"""
__doc__ = """
This is a variation on "Constants in Python" by Alex Martelli, from which the
solution idea was borrowed, and enhanced according suggestions of Zoran Isailovski.

In Python, any variable can be re-bound at will -- and modules don't let you
define special methods such as an instance's __setattr__ to stop attribute
re-binding. Easy solution (in Python 2.1 and up): use an instance as "module"...

In Python 2.1 and up, no check is made any more to force entries in sys.modules
to be actually module objects. You can install an instance object there and take
advantage of its attribute-access special methods (e.g., as in this snippet, to
prevent type rebindings. 

Usage:
  import Types.Const
  Const.magic = 23    # Bind an attribute to a type ONCE
  Const.magic = 88    # Re-bind it to a same type again
  Const.magic = "one" # But NOT re-bind it to another type: this raises Const._ConstTypeError
  del Const.magic     # Remove an named attribute
  Const.__del__()     # Remove all attributes
"""
class Const:
    class _ConstTypeError(TypeError):
        pass
    def __repr__(self):
        return "Constant type definitions."
    def __setattr__(self, name, value):
        v = self.__dict__.get(name, value)
        if type(v) is not type(value):
            raise(self._ConstTypeError, "Can't rebind %s to %s" % (type(v), type(value)))
        self.__dict__[name] = value
    def __del__(self):
        self.__dict__.clear()

import sys
sys.modules[__name__] = Const()
