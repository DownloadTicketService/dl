from distutils.core import setup
import py2exe

#data_files = []
#for files in os.listdir('.'):
#    if os.path.isfile(f1): # skip directories
#        f2 = 'images', [f1]
#        Mydata_files.append(f2)

data_files = [('.',['grant.xrc', 'newticket.xrc', 'preferences.xrc', 'upload.xrc', 'dl-wx.spec', 'dl-icon.ico'])]

setup( windows=[{ "script": "dl-wx.py",
                  "icon_resources": [(1, "dl-icon.ico")]
                }],
       data_files = data_files,
       options={"py2exe": {
              'packages':['wx']
       }})
