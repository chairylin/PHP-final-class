# PHP-final-class
 Написать класс init, от которого нельзя сделать наследника, состоящий из 3 методов:

Написать класс init, от которого нельзя сделать наследника, состоящий из 3 методов:
- create()
доступен только для методов класса
создает таблицу test (если удобнее можно создать hlblock), содержащую 5 полей:
id
целое, автоинкрементарное
script_name
строка
start_time
целое
end_time
целое
result
один вариант из 'normal',
'illegal', 'failed', 'success'

- fill()
доступен только для методов класса
заполняет таблицу случайными данными
- get()
доступен извне класса
выбирает из таблицы test, данные по критерию: result среди значений 'normal' и 'success'
В конструкторе выполняются методы create и fill