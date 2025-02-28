import xmlrpc.client

# DATOS DE CONEXION
# serverDevURL    = "https://bostogroup-qa-15714532.dev.odoo.com/"
# dataBaseDev     = "bostogroup-qa-15714532"

# userName        = "railerch"
# userPass        = "RV12041984c"
# userAPIkey      = "40cb99b69358b5d836f8abfd588dcfc2da1fa1c2"

# DATOS DE CONEXION
serverURL   = "https://bostogroup.odoo.com/"
dataBase    = "nimetrix-bostogroup-production-10630067"
userName    = "jsojo@bosto.group"
userAPIkey  = "9cdae736acd95e7605a052bcb0496c139f982039"

# CONEXION AL SERVIDOR E INICIO DE SESION
common = xmlrpc.client.ServerProxy('{}/xmlrpc/2/common'.format(serverURL))
# print(common.version())
uid = common.authenticate(dataBase, userName, userAPIkey, {})

# EJECUTAR METODOS DE MODELOS PARA CONSULTA DE DATOS
models = xmlrpc.client.ServerProxy('{}/xmlrpc/2/object'.format(serverURL))

# OBTENER LOS IDs DE LAS ORDENES REGISTRADAS
# ordersID = models.execute_kw(dataBase, uid, userAPIkey, 'sale.order', 'search', [[('state','in',('sale','done'))]], {'limit': 2})
ordersID = models.execute_kw(dataBase, uid, userAPIkey, 'sale.order', 'search', [[('state','in',('sale','done'))]], {'limit': 1})
# print(ordersID)

# OBTENER EL DETALLE DE LAS ORDENES REGISTRADAS
# [record] = models.execute_kw(dataBase, uid, userAPIkey, 'sale.order', 'read', [ordersID], {'fields': ['name', 'country_id', 'comment']})
[record] = models.execute_kw(dataBase, uid, userAPIkey, 'sale.order', 'read', [ordersID])
print(record)