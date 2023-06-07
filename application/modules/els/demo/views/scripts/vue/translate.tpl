<h3>Тест перевода</h3>

<p>
    <v-chip>HyperMethod translation test</v-chip>
</p>

<p>
    <v-chip>{{ _("HyperMethod translation test") }}</v-chip>
</p>

<h3>Множественное число (pluralization)</h3>

<p>
    <v-chip v-for="n in 20">{{ _pl("роль plural", n) }}</v-chip>
</p>