import 'package:flutter/material.dart';

class HomeScreen extends StatelessWidget {
  const HomeScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Pacientes Virtuales'),
      ),
      body: const Center(
        child: Text(
          '¡Bienvenido al Frontend con Flutter!',
          style: TextStyle(fontSize: 20),
        ),
      ),
    );
  }
}