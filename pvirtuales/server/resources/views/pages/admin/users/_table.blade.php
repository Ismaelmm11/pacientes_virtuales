@if($users->isEmpty())
    <div class="empty-state">
        <div class="empty-state-icon"><i data-lucide="user-x"></i></div>
        <div class="empty-state-title">No se encontraron usuarios</div>
        <div class="empty-state-text">Prueba a cambiar los filtros de búsqueda.</div>
    </div>
@else
    <div class="table-wrapper" style="border: none; border-radius: 0;">
        <table>
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Rol</th>
                    <th>Registro</th>
                    <th>Origen</th>
                    <th class="actions">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>
                            <div class="admin-user-row">
                                <div class="admin-user-avatar">
                                    {{ strtoupper(substr($user->first_name, 0, 1)) }}{{ strtoupper(substr($user->last_name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="patient-name">{{ $user->full_name }}</div>
                                    <div class="patient-desc">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($user->isAdmin())
                                <span class="badge badge-warning">Admin</span>
                            @elseif($user->isTeacher())
                                <span class="badge badge-primary">Profesor</span>
                            @else
                                <span class="badge badge-secondary">Alumno</span>
                            @endif
                        </td>
                        <td class="text-muted text-sm">
                            {{ $user->created_at->format('d/m/Y') }}
                        </td>
                        <td>
                            <span class="badge badge-neutral">{{ $user->auth_provider ?? 'local' }}</span>
                        </td>
                        <td class="actions">
                            <div class="row-actions">
                                @if(Route::has('admin.users.show'))
                                    <a href="{{ route('admin.users.show', $user) }}"
                                        class="btn-action" title="Ver detalle">
                                        <i data-lucide="eye"></i>
                                    </a>
                                @endif
                                @if($user->id !== auth()->id())
                                    <form action="{{ route('admin.users.destroy', $user) }}"
                                        method="POST" style="display:inline;"
                                        onsubmit="return confirm('¿Eliminar a {{ addslashes($user->full_name) }}? Esta acción no se puede deshacer.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-action btn-action-danger" title="Eliminar usuario">
                                            <i data-lucide="trash-2"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="card-footer admin-pagination-footer">
        <span class="text-sm text-muted">
            Mostrando {{ $users->firstItem() }}–{{ $users->lastItem() }} de {{ $users->total() }} usuarios
        </span>
        @if($users->hasPages())
            {{ $users->links() }}
        @endif
    </div>
@endif
