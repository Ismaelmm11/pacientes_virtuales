@if($patients->isEmpty())
    <div class="empty-state">
        <div class="empty-state-icon"><i data-lucide="user-round-x"></i></div>
        <div class="empty-state-title">No se encontraron pacientes</div>
        <div class="empty-state-text">Prueba a cambiar los filtros de búsqueda.</div>
    </div>
@else
    <div class="table-wrapper" style="border:none;border-radius:0;">
        <table>
            <thead>
                <tr>
                    <th>Paciente</th>
                    <th>Profesor</th>
                    <th>Asignatura</th>
                    <th>Modo</th>
                    <th>Estado</th>
                    <th>Simulaciones</th>
                    <th>Creado</th>
                    <th class="actions">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($patients as $patient)
                    <tr>
                        <td>
                            <div class="patient-name">{{ $patient->case_title }}</div>
                            @if($patient->patient_description)
                                <div class="patient-desc">{{ $patient->patient_description }}</div>
                            @endif
                        </td>
                        <td>
                            <div class="admin-user-row">
                                <div class="admin-user-avatar" style="width:28px;height:28px;font-size:0.68rem;">
                                    {{ strtoupper(substr($patient->createdBy?->first_name ?? '?', 0, 1)) }}{{ strtoupper(substr($patient->createdBy?->last_name ?? '', 0, 1)) }}
                                </div>
                                <span class="text-sm">{{ $patient->createdBy?->full_name ?? '—' }}</span>
                            </div>
                        </td>
                        <td><span class="text-muted text-sm">{{ $patient->subject?->name ?? '—' }}</span></td>
                        <td>
                            <span class="badge {{ $patient->mode === 'basic' ? 'badge-secondary' : 'badge-primary' }}">
                                {{ $patient->mode === 'basic' ? 'Básico' : 'Avanzado' }}
                            </span>
                        </td>
                        <td>
                            @if($patient->is_published)
                                <span class="badge badge-success">Publicado</span>
                            @else
                                <span class="badge badge-warning">Borrador</span>
                            @endif
                        </td>
                        <td><span class="badge badge-neutral">{{ $patient->test_attempts_count }}</span></td>
                        <td class="text-muted text-sm">{{ $patient->created_at->format('d/m/Y') }}</td>
                        <td class="actions">
                            <div class="row-actions">
                                <a href="{{ route('teacher.patients.preview', $patient) }}?origen=admin"
                                    class="btn-action" title="Ver y gestionar">
                                    <i data-lucide="eye"></i>
                                </a>
                                <form action="{{ route('admin.patients.destroy', $patient) }}"
                                    method="POST" style="display:inline;"
                                    onsubmit="return confirm('¿Eliminar {{ addslashes($patient->case_title) }}? Se eliminarán todas sus simulaciones.')">
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
            Mostrando {{ $patients->firstItem() }}–{{ $patients->lastItem() }} de {{ $patients->total() }} pacientes
        </span>
        @if($patients->hasPages())
            {{ $patients->links() }}
        @endif
    </div>
@endif
