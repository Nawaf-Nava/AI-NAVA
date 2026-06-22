` للتشغيل.

**الخطوة المنطقية التالية (تصحيح الخطأ الأول):**

يرجى التأكد من أنك تقوم بنسخ ولصق الكود البرمجي الموجود حصراً داخل كتل `[PYTHON]` فقط، وتجنب أي نص وصفي خارجي أو تعليقات بأحرف غير ASCII إذا كنت تقوم بتضمينها في ملف Python.

**بالإضافة إلى ذلك، لاحظت وجود خطأ سابق في عملية تثبيت مكتبة `pynput`:**

```
ERROR: Failed building wheel for evdev
Failed to build evdev
ERROR: Could not build wheels for evdev, which is required to install pyproject.toml-based projects
Failed to install pynput: Command '['/home/nawaf/nava/venv/bin/python3', '-m', 'pip', 'install', 'pynput']' returned non-zero exit status 1.
```

هذا الخطأ يعني أن مكتبة `evdev` (التي تعتمد عليها `pynput` للعمل على أنظمة Linux) فشلت في التجميع. السبب هو `error: command 'x86_64-linux-gnu-gcc' failed: No such file or directory`، مما يشير إلى أن مترجم لغة C (`gcc`) وأدوات البناء الأساسية غير مثبتة في بيئتك.

**الخطوات لتصحيح خطأ التثبيت:**

1.  **تثبيت أدوات البناء الأساسية (Build Essentials):**
    تحتاج إلى تثبيت حزمة `build-essential` التي تحتوي على `gcc` ومكتبات التطوير الأخرى اللازمة لتجميع حزم بايثون التي تحتوي على مكونات C.
    استخدم الأمر التالي في Terminal الخاص بك:

    ```bash
    [PYTHON]
    sudo apt update
    sudo apt install build-essential python3-dev
    