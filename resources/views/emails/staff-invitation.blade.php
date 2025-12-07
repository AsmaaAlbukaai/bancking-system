<x-mail::message>
# دعوة للانضمام للنظام

تمت دعوتك للانضمام للنظام البنكي كـ {{ $invitation->role }} باستخدام هذا البريد: {{ $invitation->email }}.

اضغط على الزر التالي لإكمال إنشاء حسابك:

<x-mail::button :url="$url">
إكمال التسجيل
</x-mail::button>

شكراً لك،<br>

النظام البنكي

</x-mail::message>
