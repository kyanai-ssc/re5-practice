# バリデーション拡張
バリデーションに関わる機能を追加します。

## Kuchen\Validation\Validation\Validator
- 他のフィールドのエラー情報を取得できます。たとえば、他のフィールドのデータを使用してさらに入力値検証を行う場合に有用です
- いくつかの CakePHP 組み込みバリデーションに日本語を通してしまう問題を回避します (`Kuchen\Validation\Validation\Validation` を使用)

### Table
- `Cake\ORM\Table` の代わりに `Kuchen\Validation\ORM\Table` を継承してください

### Form
- `Cake\Form\Form` の代わりに `Kuchen\Validation\Form\Form` を継承してください
- `Form::_buildSchema()` で登録のないフィールドのデータを削除します。`Form->getData()` でそのデータを取得できます

## Kuchen\Validation\Validation\Validation
- CakePHP 組み込みのバリデーション `alphaNumeric`, `email` で日本語を通してしまう問題を回避します
- `date` で区切り文字 `/`, `-` 以外を使用できないようにします (標準はこの二つと `.`, 半角スペース)
