@if($subjects->isEmpty())
    <div class="empty-state">
        <div class="empty-state-icon"><i data-lucide="book-x"></i></div>
        <div class="empty-state-title">No se encontraron asignaturas</div>
        <div class="empty-state-text">Prueba a cambiar los filtros de búsqueda.</div>
    </div>
@else
    <div class="table-wrapper" style="border:none;border-radius:0;">
        <table>
            <thead>
                <tr>
                    <th>Asignatura</th>
                    <th>Profesor</th>
                    <th>Institución</th>
                    <th>Alumnos</th>
                    <th>Pacientes</th>
                    <th>Creada</th>
                    <th class="actions">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($subjects as $subject)
                    <tr>
                        <td>
                            <div class="patient-name">{{ $subject->name }}</div>
                            @if($subject->code)
                                <div class="patient-desc">{{ $subject->code }}</div>
                            @endif
                        </td>
                        <td>
                            <div class="admin-user-row">
                                <div class="admin-user-avatar" style="width:28px;height:28px;font-size:0.68rem;">
                                    {{ strtoupper(substr($subject->creator?->first_name ?? '?', 0, 1)) }}{{ strtoupper(substr($subject->creator?->last_name ?? '', 0, 1)) }}
                                </div>
                                <span class="text-sm">{{ $subject->creator?->full_name ?? '—' }}</span>
                            </div>
                        </td>
                        <td><span class="text-muted text-sm">{{ $subject->institution ?? '—' }}</span></td>
                        <td><span class="badge badge-secondary">{{ $subject->students_count }}</span></td>
                        <td><span class="badge badge-primary">{{ $subject->patients_count }}</span></td>
                        <td class="text-muted text-sm">{{ $subject->created_at->format('d/m/Y') }}</td>
                        <td class="actions">
                            <div class="row-actions">
                                <a href="{{ route('admin.subjects.show', $subject) }}"
                                    class="btn-action" title="Ver detalle">
                                    <i data-lucide="eye"></i>
                                </a>
                                <form action="{{ route('admin.subjects.destroy', $subject) }}"
                                    method="POST" style="display:inline;"
                                    onsubmit="return confirm('¿Eliminar {{ addslashes($subject->name) }}? Se eliminarán todos sus pacientes y simulaciones.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-action btn-action-danger" title="Eliminar">
                                        <i data-lucide="trash-2"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="card-footer admin-pagination-footer">
        <span class="text-sm text-muted">
            Mostrando {{ $subjects->firstItem() }}–{{ $subjects->lastItem() }} de {{ $subjects->total() }} asignaturas
        </span>
        @if($subjects->hasPages())
            {{ $subjects->links() }}
        @endif
    </div>
@endif
