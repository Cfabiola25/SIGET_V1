# Política de Iconografía Oficial SIGET — Reicon

De conformidad con el mandato del proyecto:
1. **Uso Exclusivo de Reicon:** Todos los iconos del sistema deben provenir **única y exclusivamente** del repositorio oficial de Reicon: `https://github.com/dqev/reicon.git`.
2. **Prohibición Total de Emojis Unicode:** Queda estrictamente prohibido el uso de emojis unicode en vistas Blade, componentes, notificaciones o interfaces de usuario.
3. **Mecanismos de Invocación:**
   - Componente Blade: `<x-reicon name="trophy" class="size-5 text-amber-500" />` (o con `variant="filled"` o `:filled="true"`).
   - Helper global: `{!! reicon('football', 'size-4 text-emerald-600') !!}`.
   - Soporte class: `<x-reicon name="star" variant="filled" class="size-4 text-amber-400" />`.
