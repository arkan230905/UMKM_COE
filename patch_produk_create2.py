import re

with open('resources/views/master-data/produk/create.blade.php', 'r', encoding='utf-8') as f:
    content = f.read()

target_jual = """                    <label for="harga_jual" class="form-label">Harga Jual</label>
                    <input type="text" name="harga_jual" id="harga_jual" 
                           class="form-control" value="0" readonly style="color: #6c757d; background-color: #e9ecef;">
                    <small class="form-text text-muted">HPP belum ada, silakan buat data HPP terlebih dahulu untuk mengatur Harga Jual.</small>"""

replacement_jual = """                    <label for="harga_jual" class="form-label">Harga Jual</label>
                    <input type="text" id="harga_jual_display" 
                           class="form-control" value="HPP belum ada, silahkan kosongkan terlebih dahulu" readonly style="color: #6c757d; font-style: italic;">
                    <input type="hidden" name="harga_jual" id="harga_jual" value="0">
                    <small class="form-text text-muted">Presentase keuntungan: <span id="profit_percentage">0</span>%</small>"""

if target_jual in content:
    content = content.replace(target_jual, replacement_jual)
else:
    print("Could not find target_jual in content. Trying generic replace.")
    # Generic search to replace any harga_jual input
    content = re.sub(r'<label for="harga_jual" class="form-label">Harga Jual</label>.*?</div>', 
                     replacement_jual + '\n                </div>', 
                     content, flags=re.DOTALL)

with open('resources/views/master-data/produk/create.blade.php', 'w', encoding='utf-8') as f:
    f.write(content)

print("patched produk create blade for hidden input")
