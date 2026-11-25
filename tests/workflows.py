"""HTTP acceptance tests against a disposable, freshly seeded MariaDB database."""
import re
import subprocess
import urllib.error
import urllib.parse
import urllib.request
from datetime import date, timedelta
from http.cookiejar import CookieJar
from uuid import uuid4

BASE = 'http://127.0.0.1:8000/'
class Browser:
    def __init__(self):
        self.client = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(CookieJar()))
        self.csrf = ''

    def visit(self, path, form=None, status=200, contains=None):
        payload = None if form is None else urllib.parse.urlencode({'csrf': self.csrf, **form}).encode()
        try:
            response = self.client.open(BASE + path, data=payload)
        except urllib.error.HTTPError as error:
            response = error
        body = response.read().decode()
        assert response.status == status, (path, response.status, body[-1500:])
        if contains:
            assert contains in body, (path, contains, body[-1500:])
        token = re.search(r'name="csrf" value="([a-f0-9]+)"', body)
        if token:
            self.csrf = token[1]
        return body

def account(role):
    b = Browser()
    email = role + uuid4().hex[:10] + '@example.test'
    creds = {'email':email, 'password':'Test-only-Cafe-2026'}
    b.visit('register.php')
    b.visit('register.php', {**creds, 'name':role}, contains='Account created')
    if role == 'staff':
        subprocess.run(['mysql','-h','127.0.0.1','-u','coffee_app','coffee_corner','-e',
                        f"UPDATE users SET role='staff' WHERE email='{email}'"],check=True)
    b.visit('login.php', creds)
    return b

a, b, staff = (account(r) for r in ['Alice','Bob','staff'])
a.visit('staff.php',status=403)
a.visit('logout.php', {'csrf':'wrong'},status=403)
opts = dict(id='1',size='medium',milk='oat',syrup='vanilla',quantity='2')
def preview():
    page = a.visit('review.php',opts,contains='Total: 490 KGS')
    return re.search(r'name="key" value="([a-f0-9]+)"',page)[1]

key = preview()
page = a.visit('place-order.php',{'key':key},contains='Order placed')
order_id = re.search(r'<h3>#(\d+) · Cappuccino',page)[1]
a.visit('place-order.php',{'key':key},contains='already been placed')
assert page.count('<h3>#') == 1
assert a.visit('activity.php').count('<h3>#') == 1
assert 'Cappuccino' not in b.visit('activity.php')
b.visit('activity.php',{'action':'cancel_order','id':order_id},status=409)
for before, after in [('placed','preparing'),('preparing','ready'),('ready','collected')]:
    staff.visit('staff.php',{'from':before,'id':order_id})
    a.visit('activity.php',contains=after.capitalize())
a.visit('activity.php',{'action':'cancel_order','id':order_id},status=409)
key=preview()
page=a.visit('place-order.php',{'key':key})
second=re.search(r'<h3>#(\d+) · Cappuccino',page)[1]
a.visit('activity.php',{'action':'cancel_order','id':second},contains='Cancelled')
day=(date.today()+timedelta(days=2)).isoformat()
booking=dict(date=day,hour='09',party='2',table_id='1')
page=a.visit('reserve.php',booking,contains='Table reserved')
rid=re.search(r'<input type="hidden" name="id" value="(\d+)"><button name="action" value="cancel_booking"',page)[1]
b.visit('reserve.php',booking,status=409)
b.visit('activity.php',{'action':'cancel_booking','id':rid},status=409)
b.visit('reserve.php',{**booking,'party':'6'},status=422)
a.visit('activity.php',{'action':'cancel_booking','id':rid})
b.visit('reserve.php',booking,contains='Table reserved')
staff.visit('staff.php?day='+day,contains='Window')
item=dict(name='QA test tea',description='Warm tea',category='Tea',price='90',available='on')
page=staff.visit('manage-menu.php',{**item,'action':'create'})
item_id=re.search(r'QA test tea</h3>.*?href="\?edit=(\d+)"',page,re.S)[1]
staff.visit('manage-menu.php',{**item,'action':'update','id':item_id,'price':'95'})
a.visit('menu.php?q=QA',contains='95 KGS')
staff.visit('manage-menu.php',{'action':'delete','id':item_id})
a.visit('menu.php?q=QA',contains='No drinks match')
staff.visit('manage-menu.php',{'action':'delete','id':'1'},status=409)
a.visit('logout.php',{})
a.visit('activity.php',contains='<h1>Sign in</h1>')
print('PASS: registration, sessions, CSRF, ownership, receipt, replay, cancellation, queue, booking collision, capacity, menu CRUD and logout')
# Save freshly rendered pages from authenticated sessions for headless captures.
from pathlib import Path
preview_page = staff.visit('staff.php')
b.visit('drink.php?id=1')
pages = {'staff': preview_page, 'activity': b.visit('activity.php'), 'reserve': b.visit('reserve.php'),
         'review': b.visit('review.php', opts)}
capture = Path('/tmp/coffee-corner-pages')
capture.mkdir(exist_ok=True)
css = Path('public/style.css').read_text()
for name, html in pages.items():
    html = html.replace('<link rel="stylesheet" href="style.css">', '<style>' + css + '</style>')
    (capture / (name + '.html')).write_text(html)
