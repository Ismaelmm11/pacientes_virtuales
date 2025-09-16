import 'package:flutter/material.dart';
import 'features/test/home.dart';

void main() {
  runApp(const VirtualPatientsApp());
}

class VirtualPatientsApp extends StatelessWidget {
  const VirtualPatientsApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Pacientes Virtuales',
      theme: ThemeData(
        primarySwatch: Colors.blue,
        visualDensity: VisualDensity.adaptivePlatformDensity,
      ),
      home: const HomeScreen(),
    );
  }
}