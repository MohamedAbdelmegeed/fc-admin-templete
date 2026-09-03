# شهادات HTTPS المحلية

الشهادة الموجودة دلوقتي **self-signed مؤقتة** — اتولّدت بـ openssl عشان nginx يقدر
يقوم أصلاً. المتصفح هيدّيك تحذير «Not secure».

معيار القبول في `docs/00-setup.md` بند ٨ هو **قفل أخضر**، وده محتاج mkcert
(بيسجّل CA محلي في مخزن الشهادات بتاع النظام).

## استبدالها بشهادة موثوقة

من PowerShell **as Administrator**:

```powershell
choco install mkcert
mkcert -install
```

وبعدين من جذر المشروع:

```bash
mkcert -key-file docker/nginx/certs/local-key.pem \
       -cert-file docker/nginx/certs/local.pem \
       fc-admin.test "*.fc-admin.test" localhost
```

ثم:

```bash
docker compose restart web
```

> الملفات دي **مش** بتتعمللها commit — `.gitignore` بيستثنيها. كل مطوّر بيولّد شهادته.
