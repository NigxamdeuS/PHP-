// 画像をリサイズする処理 qiita.com/okazuki/items/3e2f4b3f4b4f4b4f4b4f より引用・改変
// ページが読み込まれたら実行
window.onload = function() {
    // 画像を選択するinput要素を取得
    var imageInput = document.getElementById('image');
    
    // input要素が存在する場合のみ処理
    if (imageInput) {
        // ファイルが選択されたときの処理
        imageInput.onchange = function() {
            // 選択されたファイルを取得
            var file = this.files[0];
            
            // ファイルが存在し、画像ファイルの場合のみ処理
            if (file && file.type.indexOf('image') >= 0) {
                // ファイルを読み込むためのオブジェクト
                var reader = new FileReader();
                
                // ファイルの読み込みが完了したときの処理
                reader.onload = function(e) {
                    // 画像オブジェクトを作成
                    var img = new Image();
                    
                    // 画像の読み込みが完了したときの処理
                    img.onload = function() {
                        // リサイズ後のサイズを計算
                        var maxSize = 1920; // 最大サイズ（ピクセル）
                        var newWidth = img.width;
                        var newHeight = img.height;
                        
                        // 幅または高さが最大サイズを超えている場合
                        if (newWidth > maxSize || newHeight > maxSize) {
                            // 幅の方が大きい場合
                            if (newWidth > newHeight) {
                                // 高さを幅に合わせて縮小
                                newHeight = (newHeight / newWidth) * maxSize;
                                newWidth = maxSize;
                            } else {
                                // 幅を高さに合わせて縮小
                                newWidth = (newWidth / newHeight) * maxSize;
                                newHeight = maxSize;
                            }
                        }
                        
                        // キャンバス要素を作成
                        var canvas = document.createElement('canvas');
                        canvas.width = newWidth;
                        canvas.height = newHeight;
                        
                        // キャンバスに画像を描画
                        var ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, newWidth, newHeight);
                        
                        // キャンバスを画像データ（Blob）に変換
                        canvas.toBlob(function(blob) {
                            // リサイズされた画像を新しいFileオブジェクトとして作成
                            var resizedFile = new File([blob], file.name, {
                                type: file.type
                            });
                            
                            // DataTransferオブジェクトを作成
                            var dataTransfer = new DataTransfer();
                            // リサイズされたファイルを追加
                            dataTransfer.items.add(resizedFile);
                            // input要素のファイルをリサイズ後のものに置き換え
                            imageInput.files = dataTransfer.files;
                        }, file.type);
                    };
                    
                    // 画像の読み込みを開始
                    img.src = e.target.result;
                };
                
                // ファイルを読み込み開始
                reader.readAsDataURL(file);
            }
        };
    }
};
