import re

with open('resources/views/master-data/produk/create.blade.php', 'r', encoding='utf-8') as f:
    content = f.read()

target_jual = """                    <label for="harga_jual" class="form-label">Harga Jual</label>
                    <input type="text" name="harga_jual" id="harga_jual" 
                           class="form-control" value="HPP belum ada, silakan buat HPP terlebih dahulu" readonly style="color: #6c757d; font-style: italic;">
                    <small class="form-text text-muted">Presentase keuntungan: <span id="profit_percentage">0</span>%</small>"""

replacement_jual = """                    <label for="harga_jual" class="form-label">Harga Jual</label>
                    <input type="text" name="harga_jual" id="harga_jual" 
                           class="form-control" value="0" readonly style="color: #6c757d; background-color: #e9ecef;">
                    <small class="form-text text-muted">HPP belum ada, silakan buat data HPP terlebih dahulu untuk mengatur Harga Jual.</small>"""

content = content.replace(target_jual, replacement_jual)

with open('resources/views/master-data/produk/create.blade.php', 'w', encoding='utf-8') as f:
    f.write(content)

print("patched produk create blade")
